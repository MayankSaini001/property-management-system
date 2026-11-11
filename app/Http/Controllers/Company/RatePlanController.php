<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RatePlan;
use App\Models\RatePlanPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RatePlanController extends Controller
{
    /**
     * List the rate plans of the logged-in Company Admin's company.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', RatePlan::class);

        $companyId = $request->user()->company_id;

        $properties = Property::where('company_id', $companyId)->orderBy('name')->get();

        $ratePlans = RatePlan::where('company_id', $companyId)
            ->with([
                'property' => function ($property) {
                    $property->withCount(['roomTypes', 'units']);
                },
            ])
            ->withCount('prices')
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->where('property_id', (int) $request->input('property_id'));
            })
            ->orderBy('property_id')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => RatePlan::where('company_id', $companyId)->count(),
            'active' => RatePlan::where('company_id', $companyId)->where('status', 'active')->count(),
            'properties' => RatePlan::where('company_id', $companyId)->distinct('property_id')->count('property_id'),
        ];

        return view('company.rate-plans.index', compact('ratePlans', 'properties', 'stats'));
    }

    /**
     * Show the New Rate Plan form.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', RatePlan::class);

        $properties = Property::where('company_id', $request->user()->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $ratePlan = new RatePlan([
            'property_id' => $request->input('property_id'),
            'meal_plan' => 'room_only',
            'status' => 'active',
            'is_default' => false,
        ]);

        return view('company.rate-plans.create', compact('ratePlan', 'properties'));
    }

    /**
     * Save a new rate plan. Prices are added on the next page.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', RatePlan::class);

        $companyId = $request->user()->company_id;

        $properties = Property::where('company_id', $companyId)->where('status', 'active')->get();

        $validated = $request->validate([
            'property_id' => ['required', Rule::in($properties->pluck('id')->all())],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('rate_plans', 'name')->where('property_id', $request->input('property_id')),
            ],
            'meal_plan' => ['required', Rule::in(array_keys(RatePlan::mealPlans()))],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $ratePlan = DB::transaction(function () use ($validated, $companyId) {
            $hasPlans = RatePlan::where('property_id', $validated['property_id'])->exists();

            // The first plan of a property is the default one. A default plan must be active.
            $makeDefault = $validated['status'] === 'active'
                && (! $hasPlans || (bool) ($validated['is_default'] ?? false));

            if ($makeDefault) {
                RatePlan::where('property_id', $validated['property_id'])->update(['is_default' => false]);
            }

            // company_id is set by the server, never taken from the form.
            return RatePlan::create([
                'company_id' => $companyId,
                'property_id' => $validated['property_id'],
                'name' => $validated['name'],
                'meal_plan' => $validated['meal_plan'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'is_default' => $makeDefault,
            ]);
        });

        return redirect()
            ->route('company.rate-plans.edit', $ratePlan)
            ->with('success', 'Rate plan created. Now set its prices below.');
    }

    /**
     * Show the details and the base prices of a rate plan.
     */
    public function edit(RatePlan $ratePlan): View
    {
        Gate::authorize('update', $ratePlan);

        $property = $ratePlan->property;
        $isHotel = $property->type === 'hotel';

        // Hotels are priced per room type, villas per unit.
        $targets = $isHotel
            ? $property->roomTypes()->orderBy('name')->get()
            : $property->units()->orderBy('name')->get();

        $prices = $ratePlan->prices()->get()->keyBy($isHotel ? 'room_type_id' : 'unit_id');

        return view('company.rate-plans.edit', compact('ratePlan', 'property', 'isHotel', 'targets', 'prices'));
    }

    /**
     * Update the details of a rate plan.
     */
    public function update(Request $request, RatePlan $ratePlan): RedirectResponse
    {
        Gate::authorize('update', $ratePlan);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('rate_plans', 'name')
                    ->where('property_id', $ratePlan->property_id)
                    ->ignore($ratePlan->id),
            ],
            'meal_plan' => ['required', Rule::in(array_keys(RatePlan::mealPlans()))],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $makeDefault = (bool) ($validated['is_default'] ?? false) || $ratePlan->is_default;

        if ($makeDefault && $validated['status'] === 'inactive') {
            throw ValidationException::withMessages([
                'status' => 'The default rate plan cannot be inactive. Make another plan the default first.',
            ]);
        }

        DB::transaction(function () use ($ratePlan, $validated, $makeDefault) {
            if ($makeDefault && ! $ratePlan->is_default) {
                RatePlan::where('property_id', $ratePlan->property_id)->update(['is_default' => false]);
            }

            $ratePlan->update([
                'name' => $validated['name'],
                'meal_plan' => $validated['meal_plan'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'is_default' => $makeDefault,
            ]);
        });

        return redirect()
            ->route('company.rate-plans.edit', $ratePlan)
            ->with('success', 'Rate plan updated successfully.');
    }

    /**
     * Save the weekday and weekend prices of every room type (hotel) or unit (villa).
     * Leaving both prices empty removes the price, so that room type or unit cannot be sold with this plan.
     */
    public function updatePrices(Request $request, RatePlan $ratePlan): RedirectResponse
    {
        Gate::authorize('update', $ratePlan);

        $property = $ratePlan->property;
        $isHotel = $property->type === 'hotel';

        $names = ($isHotel ? $property->roomTypes() : $property->units())->pluck('name', 'id');

        $validated = $request->validate([
            'prices' => ['nullable', 'array'],
            'prices.*.weekday' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'prices.*.weekend' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        $rows = $validated['prices'] ?? [];

        // Check everything first, so nothing is saved when one row is wrong.
        foreach ($rows as $targetId => $row) {
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
        }

        $column = $isHotel ? 'room_type_id' : 'unit_id';

        DB::transaction(function () use ($rows, $ratePlan, $column) {
            foreach ($rows as $targetId => $row) {
                $weekday = $row['weekday'] ?? null;
                $weekend = $row['weekend'] ?? null;

                if ($weekday === null) {
                    RatePlanPrice::where('rate_plan_id', $ratePlan->id)->where($column, (int) $targetId)->delete();

                    continue;
                }

                RatePlanPrice::updateOrCreate(
                    ['rate_plan_id' => $ratePlan->id, $column => (int) $targetId],
                    [
                        'company_id' => $ratePlan->company_id,
                        'weekday_price' => round((float) $weekday, 2),
                        'weekend_price' => round((float) $weekend, 2),
                    ]
                );
            }
        });

        return redirect()
            ->route('company.rate-plans.edit', $ratePlan)
            ->with('success', 'Prices saved successfully.');
    }

    /**
     * Activate / deactivate a rate plan. The default plan cannot be deactivated.
     */
    public function toggleStatus(RatePlan $ratePlan): RedirectResponse
    {
        Gate::authorize('update', $ratePlan);

        if ($ratePlan->isActive() && $ratePlan->is_default) {
            return back()->withErrors([
                'status' => 'The default rate plan cannot be deactivated. Make another plan the default first.',
            ]);
        }

        $ratePlan->update(['status' => $ratePlan->isActive() ? 'inactive' : 'active']);

        return back()->with(
            'success',
            $ratePlan->isActive() ? 'Rate plan activated successfully.' : 'Rate plan deactivated successfully.'
        );
    }
}