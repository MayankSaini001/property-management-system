<?php

namespace App\Services;

use App\Models\InventoryBlock;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Unit;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Query of the rooms / units that can be booked for a stay.
     *
     * A stay covers the nights check-in ... (check-out - 1), so the check-out day is free
     * for the next guest. A unit is bookable when its status is available, its room type is
     * active, it fits the guests, and it has no block and no active reservation in the dates.
     *
     * The same query is used by the search page and by the booking transaction,
     * so both always follow the same rules.
     */
    public function availableQuery(Property $property, string $checkIn, string $checkOut, int $adults, int $children)
    {
        return $property->units()
            ->bookable()
            ->freeBetween($checkIn, $checkOut)
            ->notReserved($checkIn, $checkOut)
            ->where('max_adults', '>=', $adults)
            ->where('max_children', '>=', $children)
            ->where('capacity', '>=', $adults + $children)
            ->where(function ($query) {
                $query->whereNull('room_type_id')
                    ->orWhereHas('roomType', function ($roomType) {
                        $roomType->where('status', 'active');
                    });
            });
    }

    /**
     * Find available and unavailable rooms / units for a stay.
     * Adults and children are the guests for each room.
     */
    public function search(Property $property, string $checkIn, string $checkOut, int $adults, int $children): array
    {
        $available = $this->availableQuery($property, $checkIn, $checkOut, $adults, $children)
            ->with('roomType')
            ->orderBy('name')
            ->get();

        $unavailable = $property->units()
            ->with([
                'roomType',
                'blocks' => function ($block) use ($checkIn, $checkOut) {
                    $block->overlapping($checkIn, $checkOut);
                },
                'reservationItems' => function ($item) use ($checkIn, $checkOut) {
                    $item->with('reservation')
                        ->whereDate('check_in', '<', $checkOut)
                        ->whereDate('check_out', '>', $checkIn)
                        ->whereHas('reservation', function ($reservation) {
                            $reservation->whereIn('status', Reservation::ACTIVE_STATUSES);
                        });
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
        if ($unit->reservationItems->isNotEmpty()) {
            $item = $unit->reservationItems->first();

            return 'Booked ' . ($item->reservation?->booking_reference ?? '')
                . ' (' . $item->check_in->format('d M') . ' - ' . $item->check_out->format('d M') . ')';
        }

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