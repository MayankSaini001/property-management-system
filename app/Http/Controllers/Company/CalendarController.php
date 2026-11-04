<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\InventoryBlock;
use App\Models\Property;
use App\Models\ReservationItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Booking calendar: one row per room / unit, one column per night.
     * A booking covers the nights from check-in to the day before check-out.
     * A block covers every night from its start date to its end date (both included).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Property::class);

        $properties = $request->user()->company->properties()->orderBy('name')->get();

        $property = $properties->firstWhere('id', (int) $request->input('property_id')) ?? $properties->first();

        if (! $property) {
            return view('company.calendar.index', ['property' => null, 'properties' => $properties]);
        }

        $view = in_array($request->input('view'), ['day', 'week', 'month'], true)
            ? $request->input('view')
            : 'week';

        // "Today" in the property's timezone, as a plain date.
        $todayString = now($property->timezone ?? config('app.timezone'))->toDateString();
        $today = Carbon::parse($todayString)->startOfDay();

        try {
            $anchor = Carbon::parse($request->input('date', $todayString))->startOfDay();
        } catch (\Throwable $e) {
            $anchor = $today->copy();
        }

        if ($view === 'day') {
            $start = $anchor->copy();
            $end = $anchor->copy();
            $prev = $anchor->copy()->subDay();
            $next = $anchor->copy()->addDay();
            $label = $start->format('l, d M Y');
        } elseif ($view === 'month') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth()->startOfDay();
            $prev = $start->copy()->subMonth();
            $next = $start->copy()->addMonth();
            $label = $start->format('F Y');
        } else {
            $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->addDays(6);
            $prev = $start->copy()->subWeek();
            $next = $start->copy()->addWeek();
            $label = $start->format('d M') . ' - ' . $end->format('d M Y');
        }

        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = $day->copy();
        }
        $count = count($days);
        $afterEnd = $end->copy()->addDay();

        $units = $property->units()
            ->with('roomType')
            ->get()
            ->sortBy(function ($unit) {
                return ($unit->roomType?->name ?? '') . '|' . $unit->name;
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $unitIds = $units->pluck('id');

        // Bookings and blocks that touch the visible dates.
        $itemsByUnit = ReservationItem::whereIn('unit_id', $unitIds)
            ->whereDate('check_in', '<=', $end->toDateString())
            ->whereDate('check_out', '>', $start->toDateString())
            ->whereHas('reservation', function ($reservation) {
                $reservation->whereIn('status', ['confirmed', 'checked_in', 'checked_out']);
            })
            ->with('reservation.guest')
            ->get()
            ->groupBy('unit_id');

        $blocksByUnit = InventoryBlock::whereIn('unit_id', $unitIds)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get()
            ->groupBy('unit_id');

        $bookedNights = 0;
        $reservationIds = [];
        $rows = [];

        foreach ($units as $unit) {
            $unitItems = $itemsByUnit->get($unit->id, collect());
            $unitBlocks = $blocksByUnit->get($unit->id, collect());

            $cells = [];
            $i = 0;

            while ($i < $count) {
                $day = $days[$i];
                $remaining = $count - $i;

                $item = $unitItems->first(function ($candidate) use ($day) {
                    return $candidate->check_in->lte($day) && $candidate->check_out->gt($day);
                });

                if ($item) {
                    $nights = (int) round($day->diffInDays($item->check_out));
                    $span = max(1, min($remaining, $nights));

                    $cells[] = [
                        'type' => 'reservation',
                        'span' => $span,
                        'item' => $item,
                        'clipped_left' => $item->check_in->lt($start) && $i === 0,
                        'clipped_right' => $item->check_out->gt($afterEnd),
                    ];

                    $bookedNights += $span;
                    $reservationIds[$item->reservation_id] = true;
                    $i += $span;

                    continue;
                }

                $block = $unitBlocks->first(function ($candidate) use ($day) {
                    return $candidate->start_date->lte($day) && $candidate->end_date->gte($day);
                });

                if ($block) {
                    $nights = (int) round($day->diffInDays($block->end_date)) + 1;
                    $span = max(1, min($remaining, $nights));

                    $cells[] = [
                        'type' => 'block',
                        'span' => $span,
                        'block' => $block,
                        'clipped_left' => $block->start_date->lt($start) && $i === 0,
                        'clipped_right' => $block->end_date->gte($afterEnd),
                    ];

                    $i += $span;

                    continue;
                }

                $cells[] = ['type' => 'empty', 'span' => 1];
                $i++;
            }

            $rows[] = ['unit' => $unit, 'cells' => $cells];
        }

        $capacityNights = $units->count() * $count;

        $stats = [
            'rooms' => $units->count(),
            'booked_nights' => $bookedNights,
            'occupancy' => $capacityNights > 0 ? (int) round($bookedNights / $capacityNights * 100) : 0,
            'reservations' => count($reservationIds),
        ];

        return view('company.calendar.index', compact(
            'property',
            'properties',
            'view',
            'start',
            'end',
            'days',
            'rows',
            'today',
            'prev',
            'next',
            'label',
            'stats'
        ));
    }
}