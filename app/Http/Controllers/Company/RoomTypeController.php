<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    /**
     * List room types of one hotel property.
     */
    public function index(Property $property): View
    {
        $this->authorizeHotel($property, 'viewAny');

        $roomTypes = $property->roomTypes()
            ->withCount('units')
            ->orderBy('name')
            ->paginate(10);

        return view('company.room-types.index', compact('property', 'roomTypes'));
    }

    /**
     * Show the Add Room Type form.
     */
    public function create(Property $property): View
    {
        $this->authorizeHotel($property, 'create');

        $roomType = new RoomType([
            'max_adults' => 2,
            'max_children' => 0,
            'capacity' => 2,
            'status' => 'active',
        ]);

        return view('company.room-types.create', compact('property', 'roomType'));
    }

    /**
     * Save a new room type under the property.
     */
    public function store(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeHotel($property, 'create');

        $validated = $request->validate($this->rules($request, $property));

        // company_id and property_id come from the authorized property, never from the form.
        $property->roomTypes()->create($validated + [
            'company_id' => $property->company_id,
        ]);

        return redirect()
            ->route('company.room-types.index', $property)
            ->with('success', 'Room type created successfully.');
    }

    /**
     * Show the Edit Room Type form.
     */
    public function edit(RoomType $roomType): View
    {
        Gate::authorize('update', $roomType);

        $property = $roomType->property;

        return view('company.room-types.edit', compact('property', 'roomType'));
    }

    /**
     * Update a room type.
     */
    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        Gate::authorize('update', $roomType);

        $property = $roomType->property;

        $validated = $request->validate($this->rules($request, $property, $roomType));

        $roomType->update($validated);

        return redirect()
            ->route('company.room-types.index', $property)
            ->with('success', 'Room type updated successfully.');
    }

    /**
     * Activate / deactivate a room type.
     */
    public function toggleStatus(RoomType $roomType): RedirectResponse
    {
        Gate::authorize('update', $roomType);

        $roomType->update([
            'status' => $roomType->isActive() ? 'inactive' : 'active',
        ]);

        return redirect()
            ->route('company.room-types.index', $roomType->property_id)
            ->with(
                'success',
                $roomType->isActive()
                    ? 'Room type activated successfully.'
                    : 'Room type deactivated successfully.'
            );
    }

    /**
     * Policy check first (403 for other companies), then room types are only for hotels.
     */
    private function authorizeHotel(Property $property, string $ability): void
    {
        Gate::authorize($ability, [RoomType::class, $property]);

        abort_unless($property->type === 'hotel', 404);
    }

    /**
     * Validation rules shared by store and update.
     */
    private function rules(Request $request, Property $property, ?RoomType $roomType = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('room_types', 'name')
                    ->where('property_id', $property->id)
                    ->ignore($roomType?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_adults' => ['required', 'integer', 'min:1', 'max:20'],
            'max_children' => ['required', 'integer', 'min:0', 'max:20'],
            'capacity' => [
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
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}