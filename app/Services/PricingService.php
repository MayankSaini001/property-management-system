<?php

namespace App\Services;

use App\Models\RatePlan;
use App\Models\RatePlanPrice;
use App\Models\RatePlanSeason;
use App\Models\Unit;
use Carbon\Carbon;

class PricingService
{
    /**
     * Price of one hotel room or villa unit for a stay, with a rate plan.
     * Returns null when the plan has no price for it.
     */
    public function quote(RatePlan $ratePlan, Unit $unit, $checkIn, $checkOut): ?array
    {
        $isHotel = $ratePlan->property->type === 'hotel';

        // Hotels are priced per room type, villas per unit.
        $column = $isHotel ? 'room_type_id' : 'unit_id';
        $targetId = $isHotel ? $unit->room_type_id : $unit->id;

        if (! $targetId) {
            return null;
        }

        return $this->quoteForTarget($ratePlan, $column, (int) $targetId, $checkIn, $checkOut);
    }

    /**
     * Price of one room type (room_type_id) or villa unit (unit_id) for a stay.
     *
     * The stay covers the nights from check-in to the day before check-out.
     * For every night the price is taken from the season that covers the night, if the
     * season has a price for this target. Otherwise the base price is used.
     * Friday and Saturday nights use the weekend price (see RatePlan::WEEKEND_DAYS).
     *
     * Money is added in minor units (paise / cents) so no rounding error can happen.
     */
    public function quoteForTarget(RatePlan $ratePlan, string $column, int $targetId, $checkIn, $checkOut): ?array
    {
        $start = Carbon::parse($checkIn)->startOfDay();
        $end = Carbon::parse($checkOut)->startOfDay();

        if ($end->lte($start) || $start->diffInDays($end) > 366) {
            return null;
        }

        $base = RatePlanPrice::where('rate_plan_id', $ratePlan->id)
            ->where($column, $targetId)
            ->first();

        if (! $base) {
            return null;
        }

        // Seasons that touch the nights of the stay and have a price for this target.
        $seasons = RatePlanSeason::where('rate_plan_id', $ratePlan->id)
            ->whereDate('start_date', '<', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->with([
                'prices' => function ($prices) use ($column, $targetId) {
                    $prices->where($column, $targetId);
                },
            ])
            ->get()
            ->filter(function ($season) {
                return $season->prices->isNotEmpty();
            });

        $nights = [];
        $cents = 0;

        for ($day = $start->copy(); $day->lt($end); $day->addDay()) {
            $weekend = in_array($day->dayOfWeek, RatePlan::WEEKEND_DAYS, true);

            $season = $seasons->first(function ($candidate) use ($day) {
                return $candidate->start_date->lte($day) && $candidate->end_date->gte($day);
            });

            $row = $season ? $season->prices->first() : $base;
            $price = $weekend ? $row->weekend_price : $row->weekday_price;
            $amount = (int) round(((float) $price) * 100);

            $cents += $amount;

            $nights[] = [
                'date' => $day->copy(),
                'type' => $weekend ? 'weekend' : 'weekday',
                'season' => $season?->name,
                'price' => number_format($amount / 100, 2, '.', ''),
            ];
        }

        return [
            'nights' => $nights,
            'count' => count($nights),
            'total' => number_format($cents / 100, 2, '.', ''),
            'cents' => $cents,
        ];
    }
}
