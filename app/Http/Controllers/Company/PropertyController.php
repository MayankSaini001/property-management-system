<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /**
     * List properties of the logged-in Company Admin's company only,
     * with live search and filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Property::class);

        $company = $request->user()->company;

        $properties = $company->properties()
            ->withCount(['units', 'roomTypes'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->input('q') . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when(
                $request->filled('type') && array_key_exists($request->input('type'), Property::types()),
                function ($query) use ($request) {
                    $query->where('type', $request->input('type'));
                }
            )
            ->when(
                in_array($request->input('status'), ['active', 'inactive'], true),
                function ($query) use ($request) {
                    $query->where('status', $request->input('status'));
                }
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        // Summary numbers for the cards (not affected by the filters).
        $stats = [
            'total' => $company->properties()->count(),
            'hotels' => $company->properties()->where('type', 'hotel')->count(),
            'villas' => $company->properties()->where('type', 'villa')->count(),
            'active' => $company->properties()->where('status', 'active')->count(),
        ];

        return view('company.properties.index', compact('properties', 'stats'));
    }

    /**
     * Show the Add Property form.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Property::class);

        $company = $request->user()->company;

        // Default values come from the company settings.
        $property = new Property([
            'type' => 'hotel',
            'currency' => $company->currency,
            'timezone' => $company->timezone,
            'status' => 'active',
        ]);

        return view('company.properties.create', compact('property'));
    }

    /**
     * Save a new property under the logged-in user's company.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Property::class);

        $validated = $request->validate($this->rules());

        $company = $request->user()->company;

        $validated['slug'] = $this->uniqueSlug($company->id, $validated['name']);

        // company_id is set by the relation, never taken from the form.
        $company->properties()->create($validated);

        return redirect()
            ->route('company.properties.index')
            ->with('success', 'Property created successfully.');
    }

    /**
     * Show the Edit Property form.
     */
    public function edit(Property $property): View
    {
        Gate::authorize('update', $property);

        return view('company.properties.edit', compact('property'));
    }

    /**
     * Update a property.
     */
    public function update(Request $request, Property $property): RedirectResponse
    {
        Gate::authorize('update', $property);

        $validated = $request->validate($this->rules());

        $property->update($validated);

        return redirect()
            ->route('company.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Activate / deactivate a property (no deleting, so booking history stays safe).
     * Goes back to the same page, so the search and filters stay as they were.
     */
    public function toggleStatus(Property $property): RedirectResponse
    {
        Gate::authorize('update', $property);

        $property->update([
            'status' => $property->isActive() ? 'inactive' : 'active',
        ]);

        return back()->with(
            'success',
            $property->isActive()
                ? 'Property activated successfully.'
                : 'Property deactivated successfully.'
        );
    }

    /**
     * Validation rules shared by store and update.
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Property::types()))],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', Rule::in(array_keys(Property::countries()))],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'timezone' => ['required', Rule::in(array_keys(Property::timezones()))],
            'currency' => ['required', Rule::in(array_keys(Property::currencies()))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * Slug that is unique inside one company (soft-deleted rows included).
     */
    private function uniqueSlug(int $companyId, string $name): string
    {
        $base = Str::slug($name) ?: 'property';
        $slug = $base;
        $counter = 1;

        while (
            Property::withTrashed()
                ->where('company_id', $companyId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}