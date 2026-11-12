<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\RatePlan;
use App\Models\RatePlanSeason;
use App\Models\RatePlanSeasonPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RatePlanSeasonController extends Controller
{
    /**
     * Show the Add Season form.
     */
    public function create(RatePlan $ratePlan): View
    {
        Gate::authorize('update', $ratePlan);

        $season = new RatePlanSeason();
        $prices = collect();

        return view('company.rate-plans.seasons.create', array_merge(
            $this->formData($ratePlan),
            compact('ratePlan', 'season', 'prices')
        ));
    }

    /**
     * Save a new season with its prices.
     */
    public function store(Request $request, RatePlan $ratePlan): RedirectResponse
    {
        Gate::authorize('update', $ratePlan);

        [$validated, $rows, $column] = $this->validated($request, $ratePlan);

        DB::transaction(function () use ($ratePlan, $validated, $rows, $column) {
            $season = RatePlanSeason::create([
                'company_id' => $ratePlan->company_id,
                'rate_plan_id' => $ratePlan->id,
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            $this->savePrices($season, $rows, $column);
        });

        return redirect()
            ->route('company.rate-plans.edit', $ratePlan)
            ->with('success', 'Season added successfully.');
    }

    /**
     * Show the Edit Season form.
     */
    public function edit(RatePlanSeason $season): View
    {
        $ratePlan = $season->ratePlan;

        Gate::authorize('update', $ratePlan);

        $data = $this->formData($ratePlan);

        $prices = $season->prices()->get()->keyBy($data['isHotel'] ? 'room_type_id' : 'unit_id');

        return view('company.rate-plans.seasons.edit', array_merge(
            $data,
            compact('ratePlan', 'season', 'prices')
        ));
    }

    /**
     * Update a season and its prices.
     */
    public function update(Request $request, RatePlanSeason $season): RedirectResponse
    {
        $ratePlan = $season->ratePlan;

        Gate::authorize('update', $ratePlan);

        [$validated, $rows, $column] = $this->validated($request, $ratePlan, $season);

        DB::transaction(function () use ($season, $validated, $rows, $column) {
            $season->update([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            $season->prices()->delete();

            $this->savePrices($season, $rows, $column);
        });

        return redirect()
            ->route('company.rate-plans.edit', $ratePlan)
            ->with('success', 'Season updated successfully.');
    }

    /**
     * Remove a season. Bookings that are already saved keep their own prices.
     */
    public function destroy(RatePlanSeason $season): RedirectResponse
    {
        $ratePlan = $season->ratePlan;

        Gate::authorize('update', $ratePlan);

        $season->delete();

        return redirect()
            ->route('company.rate-plans.edit', $ratePlan)
            ->with('success', 'Season removed successfully.');
    }

    /**
     * Data needed by the season form: the property and the room types or units to price.
     */
    private function formData(RatePlan $ratePlan): array
    {
        $property = $ratePlan->property;
        $isHotel = $property->type === 'hotel';

        $targets = $isHotel
            ? $property->roomTypes()->orderBy('name')->get()
            : $property->units()->orderBy('name')->get();

        return compact('property', 'isHotel', 'targets');
    }

    /**
     * Validate the season form.
     * Returns the validated fields, the prices to save (target id => [weekday, weekend]) and the price column.
     */
    private function validated(Request $request, RatePlan $ratePlan, ?RatePlanSeason $season = null): array
    {
        $property = $ratePlan->property;
        $isHotel = $property->type === 'hotel';
        $column = $isHotel ? 'room_type_id' : 'unit_id';

        $names = ($isHotel ? $property->roomTypes() : $property->units())->pluck('name', 'id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'prices' => ['nullable', 'array'],
            'prices.*.weekday' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'prices.*.weekend' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        // Seasons of one rate plan cannot overlap, so every night has only one price.
        $overlap = RatePlanSeason::where('rate_plan_id', $ratePlan->id)
            ->when($season, function ($query) use ($season) {
                $query->where('id', '!=', $season->id);
            })
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->first();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'These dates overlap the season "' . $overlap->name . '" ('
                    . $overlap->start_date->format('d M') . ' - ' . $overlap->end_date->format('d M Y') . ').',
            ]);
        }

        $rows = [];

        foreach (($validated['prices'] ?? []) as $targetId => $row) {
            if (! $names->has((int) $targetId)) {
                throw ValidationException::withMessages(['prices' => 'One of the prices is for an unknown room type or unit.']);
            }

            $weekday = $row['weekday'] ?? null;
            $weekend = $row['weekend'] ?? null;

            if (($weekday === null) !== ($weekend === null)) {
                throw ValidationException::withMessages([
                    'prices' => 'Enter both the weekday and the weekend price for ' . $names[(int) $targetId] . '.',
                ]);
            }

            if ($weekday !== null) {
                $rows[(int) $targetId] = [round((float) $weekday, 2), round((float) $weekend, 2)];
            }
        }

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'prices' => 'Enter the price of at least one ' . ($isHotel ? 'room type' : 'villa unit') . ' for this season.',
            ]);
        }

        return [$validated, $rows, $column];
    }

    private function savePrices(RatePlanSeason $season, array $rows, string $column): void
    {
        foreach ($rows as $targetId => [$weekday, $weekend]) {
            RatePlanSeasonPrice::create([
                'company_id' => $season->company_id,
                'season_id' => $season->id,
                $column => $targetId,
                'weekday_price' => $weekday,
                'weekend_price' => $weekend,
            ]);
        }
    }
}