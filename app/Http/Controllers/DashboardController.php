<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard for both roles. The Super Admin keeps the platform dashboard,
     * a Company Admin gets the operations dashboard of their company.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return view('dashboard');
        }

        $company = $user->company;

        $properties = $company->properties()->orderBy('name')->get();

        // Empty property filter = all properties of the company.
        $selected = $properties->firstWhere('id', (int) $request->input('property_id'));
        $propertyId = $selected?->id;

        $timezone = $selected?->timezone
            ?? $properties->first()?->timezone
            ?? config('app.timezone');

        $todayString = now($timezone)->toDateString();
        $today = Carbon::parse($todayString)->startOfDay();

        $reservations = function () use ($company, $propertyId) {
            return Reservation::where('company_id', $company->id)
                ->when($propertyId, function ($query) use ($propertyId) {
                    $query->where('property_id', $propertyId);
                });
        };

        $units = function () use ($company, $propertyId) {
            return Unit::where('company_id', $company->id)
                ->when($propertyId, function ($query) use ($propertyId) {
                    $query->where('property_id', $propertyId);
                });
        };

        // ---- Today: arrivals and departures ----
        $arrivalsRemaining = $reservations()->where('status', 'confirmed')
            ->whereDate('check_in', $todayString)->count();
        $arrivalsDone = $reservations()->whereIn('status', ['checked_in', 'checked_out'])
            ->whereDate('check_in', $todayString)->count();

        $departuresRemaining = $reservations()->where('status', 'checked_in')
            ->whereDate('check_out', $todayString)->count();
        $departuresDone = $reservations()->where('status', 'checked_out')
            ->whereDate('check_out', $todayString)->count();

        $todayArrivals = $reservations()
            ->with(['guest', 'items.unit', 'property'])
            ->where('status', 'confirmed')
            ->whereDate('check_in', $todayString)
            ->orderBy('id')
            ->limit(5)
            ->get();

        $todayDepartures = $reservations()
            ->with(['guest', 'items.unit', 'property'])
            ->where('status', 'checked_in')
            ->whereDate('check_out', $todayString)
            ->orderBy('id')
            ->limit(5)
            ->get();

        // ---- Rooms and units ----
        $statusCounts = $units()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $roomStats = [
            'total' => (int) $statusCounts->sum(),
            'available' => (int) ($statusCounts['available'] ?? 0),
            'maintenance' => (int) ($statusCounts['maintenance'] ?? 0),
            'out_of_service' => (int) ($statusCounts['out_of_service'] ?? 0),
            'blocked_today' => $units()
                ->whereHas('blocks', function ($block) use ($todayString) {
                    $block->whereDate('start_date', '<=', $todayString)
                        ->whereDate('end_date', '>=', $todayString);
                })
                ->count(),
        ];

        // ---- Guests (guest profiles belong to the company) ----
        $guestTotal = Guest::where('company_id', $company->id)->count();

        $guestStats = [
            'total' => $guestTotal,
            'new_this_month' => Guest::where('company_id', $company->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'with_email' => Guest::where('company_id', $company->id)->whereNotNull('email')->count(),
            'with_phone' => Guest::where('company_id', $company->id)->whereNotNull('phone')->count(),
        ];

        // ---- Next 7 days ----
        $first = $today->copy();
        $last = $today->copy()->addDays(6);
        $allStatuses = ['confirmed', 'checked_in', 'checked_out'];

        $arrivalsByDay = $reservations()->whereIn('status', $allStatuses)
            ->whereBetween('check_in', [$first->toDateString(), $last->toDateString()])
            ->selectRaw('check_in as day, COUNT(*) as total')
            ->groupBy('check_in')
            ->pluck('total', 'day');

        $departuresByDay = $reservations()->whereIn('status', $allStatuses)
            ->whereBetween('check_out', [$first->toDateString(), $last->toDateString()])
            ->selectRaw('check_out as day, COUNT(*) as total')
            ->groupBy('check_out')
            ->pluck('total', 'day');

        // Rooms that are occupied for at least one night of the next 7 days.
        $items = ReservationItem::where('company_id', $company->id)
            ->whereDate('check_in', '<=', $last->toDateString())
            ->whereDate('check_out', '>', $first->toDateString())
            ->whereHas('reservation', function ($reservation) use ($propertyId) {
                $reservation->whereIn('status', Reservation::ACTIVE_STATUSES)
                    ->when($propertyId, function ($query) use ($propertyId) {
                        $query->where('property_id', $propertyId);
                    });
            })
            ->get(['unit_id', 'check_in', 'check_out', 'adults', 'children']);

        $series = [];

        for ($day = $first->copy(); $day->lte($last); $day->addDay()) {
            $key = $day->toDateString();

            $occupiedItems = $items->filter(function ($item) use ($day) {
                return $item->check_in->lte($day) && $item->check_out->gt($day);
            });

            $occupied = $occupiedItems->count();

            $series[] = [
                'date' => $day->copy(),
                'arrivals' => (int) ($arrivalsByDay[$key] ?? 0),
                'departures' => (int) ($departuresByDay[$key] ?? 0),
                'stayovers' => $occupiedItems->filter(function ($item) use ($day) {
                    return $item->check_in->lt($day);
                })->count(),
                'guests' => (int) $occupiedItems->sum(function ($item) {
                    return $item->adults + $item->children;
                }),
                'occupied' => $occupied,
                'occupancy' => $roomStats['total'] > 0
                    ? min(100, (int) round($occupied / $roomStats['total'] * 100))
                    : 0,
            ];
        }

        return view('company.dashboard', [
            'company' => $company,
            'properties' => $properties,
            'selected' => $selected,
            'today' => $today,
            'arrivalsRemaining' => $arrivalsRemaining,
            'arrivalsDone' => $arrivalsDone,
            'departuresRemaining' => $departuresRemaining,
            'departuresDone' => $departuresDone,
            'todayArrivals' => $todayArrivals,
            'todayDepartures' => $todayDepartures,
            'roomStats' => $roomStats,
            'guestStats' => $guestStats,
            'series' => $series,
        ]);
    }
}