<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * List rooms (hotel) or units (villa) of one property, with search and filters.
     */
    public function index(Request $request, Property $property): View
    {
        Gate::authorize('viewAny', [Unit::class, $property]);

        $today = now($property->timezone)->toDateString();

        $units = $property->units()
            ->with('roomType')
            ->withCount([
                'blocks as blocked_now' => function ($block) use ($today) {
                    $block->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today);
                },
            ])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('q') . '%');
            })
            ->when(
                $request->filled('status') && array_key_exists($request->input('status'), Unit::statuses()),
                function ($query) use ($request) {
                    $query->where('status', $request->input('status'));
                }
            )
            ->when($request->filled('room_type_id'), function ($query) use ($request) {
                $query->where('room_type_id', (int) $request->input('room_type_id'));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roomTypes = $property->type === 'hotel'
            ? $property->roomTypes()->orderBy('name')->get()
            : collect();

        return view('company.units.index', compact('property', 'units', 'roomTypes'));
    }

    /**
     * Show the Add Room / Add Unit form.
     */
    public function create(Property $property): View|RedirectResponse
    {
        Gate::authorize('create', [Unit::class, $property]);

        $roomTypes = $property->roomTypes()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // A hotel room must belong to a room type, so one is needed first.
        if ($property->type === 'hotel' && $roomTypes->isEmpty()) {
            return redirect()
                ->route('company.room-types.index', $property)
                ->with('success', 'Please add a room type first. Hotel rooms are created under a room type.');
        }

        $unit = new Unit([
            'max_adults' => 2,
            'max_children' => 0,
            'capacity' => 2,
            'status' => 'available',
        ]);

        return view('company.units.create', compact('property', 'unit', 'roomTypes'));
    }

    /**
     * Save a new room / unit under the property.
     */
    public function store(Request $request, Property $property): RedirectResponse
    {
        Gate::authorize('create', [Unit::class, $property]);

        $validated = $request->validate($this->rules($request, $property));

        // company_id and property_id come from the authorized property, never from the form.
        $property->units()->create(
            $this->buildData($property, $validated) + ['company_id' => $property->company_id]
        );

        return redirect()
            ->route('company.units.index', $property)
            ->with('success', $property->type === 'hotel' ? 'Room created successfully.' : 'Unit created successfully.');
    }

    /**
     * Show the Edit Room / Unit form.
     */
    public function edit(Unit $unit): View
    {
        Gate::authorize('update', $unit);

        $property = $unit->property;

        $roomTypes = $property->roomTypes()->orderBy('name')->get();

        return view('company.units.edit', compact('property', 'unit', 'roomTypes'));
    }

    /**
     * Update a room / unit.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        Gate::authorize('update', $unit);

        $property = $unit->property;

        $validated = $request->validate($this->rules($request, $property, $unit));

        $unit->update($this->buildData($property, $validated));

        return redirect()
            ->route('company.units.index', $property)
            ->with('success', $property->type === 'hotel' ? 'Room updated successfully.' : 'Unit updated successfully.');
    }

    /**
     * Quick status change from the list (available / out of service / maintenance).
     */
    public function updateStatus(Request $request, Unit $unit): RedirectResponse
    {
        Gate::authorize('update', $unit);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Unit::statuses()))],
        ]);

        $unit->update($validated);

        return redirect()
            ->route('company.units.index', $unit->property_id)
            ->with('success', 'Status changed to ' . Unit::statuses()[$unit->status] . '.');
    }

    /**
     * Build the data to save. Unit type is decided by the property type, never by the form.
     * A hotel room takes its capacity from its room type.
     */
    private function buildData(Property $property, array $validated): array
    {
        if ($property->type === 'hotel') {
            $roomType = RoomType::where('property_id', $property->id)
                ->findOrFail($validated['room_type_id']);

            return [
                'room_type_id' => $roomType->id,
                'unit_type' => 'room',
                'name' => $validated['name'],
                'max_adults' => $roomType->max_adults,
                'max_children' => $roomType->max_children,
                'capacity' => $roomType->capacity,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ];
        }

        return [
            'room_type_id' => null,
            'unit_type' => 'villa',
            'name' => $validated['name'],
            'max_adults' => $validated['max_adults'],
            'max_children' => $validated['max_children'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];
    }

    /**
     * Validation rules shared by store and update.
     */
    private function rules(Request $request, Property $property, ?Unit $unit = null): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')
                    ->where('property_id', $property->id)
                    ->ignore($unit?->id),
            ],
            'status' => ['required', Rule::in(array_keys(Unit::statuses()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($property->type === 'hotel') {
            $rules['room_type_id'] = [
                'required',
                Rule::exists('room_types', 'id')
                    ->where('property_id', $property->id)
                    ->whereNull('deleted_at'),
            ];

            return $rules;
        }

        // Villa unit: capacity is entered directly.
        $rules['max_adults'] = ['required', 'integer', 'min:1', 'max:20'];
        $rules['max_children'] = ['required', 'integer', 'min:0', 'max:20'];
        $rules['capacity'] = [
            'required',
            'integer',
            'min:1',
            'max:40',
            function ($attribute, $value, $fail) use ($request) {
                $max = (int) $request->input('max_adults') + (int) $request->input('max_children');

                if ((int) $value > $max) {
                    $fail("Capacity cannot be more than max adults plus max children ({$max}).");
                }
            },
        ];

        return $rules;
    }
}