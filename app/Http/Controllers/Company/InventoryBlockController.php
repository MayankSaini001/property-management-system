<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\InventoryBlock;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryBlockController extends Controller
{
    /**
     * List maintenance / owner-use blocks of one property.
     */
    public function index(Request $request, Property $property): View
    {
        Gate::authorize('viewAny', [InventoryBlock::class, $property]);

        $today = now($property->timezone)->toDateString();
        $when = in_array($request->query('when'), ['upcoming', 'past', 'all'], true)
            ? $request->query('when')
            : 'upcoming';

        $query = $property->inventoryBlocks()->with('unit');

        if ($when === 'upcoming') {
            $query->whereDate('end_date', '>=', $today)->orderBy('start_date');
        } elseif ($when === 'past') {
            $query->whereDate('end_date', '<', $today)->orderByDesc('start_date');
        } else {
            $query->orderByDesc('start_date');
        }

        $blocks = $query->paginate(15)->withQueryString();

        return view('company.blocks.index', compact('property', 'blocks', 'when', 'today'));
    }

    /**
     * Show the Add Block form.
     */
    public function create(Request $request, Property $property): View|RedirectResponse
    {
        Gate::authorize('create', [InventoryBlock::class, $property]);

        $units = $property->units()->orderBy('name')->get();

        if ($units->isEmpty()) {
            return redirect()
                ->route('company.units.index', $property)
                ->with('success', 'Please add a room or unit first. Blocks are created for a room or unit.');
        }

        $block = new InventoryBlock([
            'block_type' => 'maintenance',
            'unit_id' => $request->query('unit'),
        ]);

        return view('company.blocks.create', compact('property', 'units', 'block'));
    }

    /**
     * Save a new block.
     */
    public function store(Request $request, Property $property): RedirectResponse
    {
        Gate::authorize('create', [InventoryBlock::class, $property]);

        $today = now($property->timezone)->toDateString();

        $validated = $request->validate([
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')
                    ->where('property_id', $property->id)
                    ->whereNull('deleted_at'),
            ],
            'block_type' => ['required', Rule::in(array_keys(InventoryBlock::types()))],
            'start_date' => ['required', 'date', 'after_or_equal:' . $today],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    $overlaps = InventoryBlock::where('unit_id', $request->input('unit_id'))
                        ->whereDate('start_date', '<=', $value)
                        ->whereDate('end_date', '>=', $request->input('start_date'))
                        ->exists();

                    if ($overlaps) {
                        $fail('This room or unit already has a block in these dates.');
                    }
                },
            ],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // company_id, property_id and created_by come from the server, never from the form.
        $property->inventoryBlocks()->create($validated + [
            'company_id' => $property->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('company.blocks.index', $property)
            ->with('success', 'Block added successfully.');
    }

    /**
     * Remove a block (soft delete).
     */
    public function destroy(InventoryBlock $block): RedirectResponse
    {
        Gate::authorize('delete', $block);

        $propertyId = $block->property_id;

        $block->delete();

        return redirect()
            ->route('company.blocks.index', $propertyId)
            ->with('success', 'Block removed successfully.');
    }
}