<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuestController extends Controller
{
    /**
     * List guests of the logged-in Company Admin's company, with live search.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Guest::class);

        $companyId = $request->user()->company_id;

        $guests = Guest::where('company_id', $companyId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . trim($request->input('q')) . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$term])
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(12)
            ->withQueryString();

        // Summary numbers for the cards (not affected by the search).
        $stats = [
            'total' => Guest::where('company_id', $companyId)->count(),
            'new_this_month' => Guest::where('company_id', $companyId)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'with_email' => Guest::where('company_id', $companyId)->whereNotNull('email')->count(),
            'with_phone' => Guest::where('company_id', $companyId)->whereNotNull('phone')->count(),
        ];

        return view('company.guests.index', compact('guests', 'stats'));
    }

    /**
     * Show the Add Guest form.
     */
    public function create(): View
    {
        Gate::authorize('create', Guest::class);

        return view('company.guests.create', ['guest' => new Guest()]);
    }

    /**
     * Save a new guest under the logged-in user's company.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Guest::class);

        $companyId = $request->user()->company_id;

        $validated = $request->validate($this->rules($companyId), $this->messages());

        // company_id comes from the logged-in user, never from the form.
        $guest = Guest::create($validated + ['company_id' => $companyId]);

        return redirect()
            ->route('company.guests.show', $guest)
            ->with('success', 'Guest created successfully.');
    }

    /**
     * Show one guest profile.
     */
    public function show(Guest $guest): View
    {
        Gate::authorize('view', $guest);

        return view('company.guests.show', compact('guest'));
    }

    /**
     * Show the Edit Guest form.
     */
    public function edit(Guest $guest): View
    {
        Gate::authorize('update', $guest);

        return view('company.guests.edit', compact('guest'));
    }

    /**
     * Update a guest.
     */
    public function update(Request $request, Guest $guest): RedirectResponse
    {
        Gate::authorize('update', $guest);

        $validated = $request->validate($this->rules($guest->company_id, $guest), $this->messages());

        $guest->update($validated);

        return redirect()
            ->route('company.guests.show', $guest)
            ->with('success', 'Guest updated successfully.');
    }

    /**
     * Validation rules shared by store and update.
     */
    private function rules(int $companyId, ?Guest $guest = null): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable',
                'required_without:phone',
                'email',
                'max:255',
                Rule::unique('guests', 'email')
                    ->where('company_id', $companyId)
                    ->ignore($guest?->id),
            ],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', Rule::in(array_keys(Property::countries()))],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'preferences' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function messages(): array
    {
        return [
            'email.required_without' => 'Please enter an email or a phone number.',
            'phone.required_without' => 'Please enter a phone number or an email.',
            'email.unique' => 'A guest with this email already exists in your company.',
            'phone.regex' => 'The phone number may contain only digits, +, -, spaces and brackets.',
        ];
    }
}