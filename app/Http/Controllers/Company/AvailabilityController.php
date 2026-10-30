<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\InventoryBlock;
use App\Models\Property;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * Search which rooms or villa units can be booked for the given dates and guests.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Property::class);

        // Only active properties of the logged-in user's company can be searched.
        $properties = $request->user()
            ->company
            ->properties()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $submitted = $request->filled('property_id')
            || $request->filled('check_in')
            || $request->filled('check_out');

        $search = null;
        $searchErrors = [];

        if ($submitted) {
            $property = $properties->firstWhere('id', (int) $request->input('property_id'));
            $today = now($property?->timezone ?? config('app.timezone'))->toDateString();

            $validator = Validator::make($request->all(), [
                'property_id' => ['required', Rule::in($properties->pluck('id')->all())],
                'check_in' => ['required', 'date', 'after_or_equal:' . $today],
                'check_out' => ['required', 'date', 'after:check_in'],
                'adults' => ['required', 'integer', 'min:1', 'max:20'],
                'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            ]);

            $validator->after(function ($validator) use ($request) {
                if ($validator->errors()->isEmpty()) {
                    $nights = Carbon::parse($request->input('check_in'))
                        ->diffInDays(Carbon::parse($request->input('check_out')));

                    if ($nights > 60) {
                        $validator->errors()->add('check_out', 'A stay can be searched for up to 60 nights.');
                    }
                }
            });

            if ($validator->fails()) {
                $searchErrors = $validator->errors()->all();
            } else {
                $search = $this->search(
                    $property,
                    $request->input('check_in'),
                    $request->input('check_out'),
                    (int) $request->input('adults'),
                    (int) $request->input('children', 0)
                );
            }
        }

        return view('company.availability.index', compact('properties', 'search', 'searchErrors', 'submitted'));
    }

    /**
     * Find available and unavailable rooms / units for a stay.
     *
     * A stay covers the nights check-in ... (check-out - 1), so the check-out day is free
     * for the next guest. Reservations are not part of the system yet; when they are added,
     * their dates must be excluded here in the same way as the blocks.
     */
    private function search(Property $property, string $checkIn, string $checkOut, int $adults, int $children): array
    {
        $available = $property->units()
            ->with('roomType')
            ->bookable()
            ->freeBetween($checkIn, $checkOut)
            ->where('max_adults', '>=', $adults)
            ->where('max_children', '>=', $children)
            ->where('capacity', '>=', $adults + $children)
            ->where(function ($query) {
                $query->whereNull('room_type_id')
                    ->orWhereHas('roomType', function ($roomType) {
                        $roomType->where('status', 'active');
                    });
            })
            ->orderBy('name')
            ->get();

        $unavailable = $property->units()
            ->with([
                'roomType',
                'blocks' => function ($block) use ($checkIn, $checkOut) {
                    $block->overlapping($checkIn, $checkOut);
                },
            ])
            ->whereNotIn('id', $available->pluck('id'))
            ->orderBy('name')
            ->get()
            ->each(function (Unit $unit) use ($adults, $children) {
                $unit->setAttribute('reason', $this->reason($unit, $adults, $children));
            });

        $start = Carbon::parse($checkIn)->startOfDay();
        $end = Carbon::parse($checkOut)->startOfDay();

        return [
            'property' => $property,
            'check_in' => $start,
            'check_out' => $end,
            'nights' => (int) $start->diffInDays($end),
            'adults' => $adults,
            'children' => $children,
            'available' => $available,
            'unavailable' => $unavailable,
        ];
    }

    /**
     * Why a room or unit cannot be booked for the searched stay.
     */
    private function reason(Unit $unit, int $adults, int $children): string
    {
        if ($unit->blocks->isNotEmpty()) {
            $block = $unit->blocks->first();

            return (InventoryBlock::types()[$block->block_type] ?? 'Blocked')
                . ' block (' . $block->start_date->format('d M') . ' - ' . $block->end_date->format('d M') . ')';
        }

        if ($unit->status !== 'available') {
            return Unit::statuses()[$unit->status] ?? ucfirst($unit->status);
        }

        if ($unit->roomType && $unit->roomType->status !== 'active') {
            return 'Room type is inactive';
        }

        return 'Too small for ' . $adults . ' adult' . ($adults > 1 ? 's' : '')
            . ($children > 0 ? ' + ' . $children . ' child' . ($children > 1 ? 'ren' : '') : '');
    }
}