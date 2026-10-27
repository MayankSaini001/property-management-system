<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display all companies.
     */
    public function index()
    {
        $companies = Company::latest()->paginate(10);

        return view('superadmin.companies.index', compact('companies'));
    }

    /**
     * Show the create company form.
     */
    public function create()
    {
        return view('superadmin.companies.create');
    }

    /**
     * Save a new company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'timezone'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $baseSlug = Str::slug($validated['name']);

        if ($baseSlug === '') {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Please enter a valid company name.']);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Company::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        Company::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'currency' => strtoupper($validated['currency']),
            'timezone' => $validated['timezone'],
            'slug' => $slug,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Show the edit company form.
     */
    public function edit(Company $company)
    {
        return view('superadmin.companies.edit', compact('company'));
    }

    /**
     * Update an existing company.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'timezone'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validated['currency'] = strtoupper($validated['currency']);

        $company->update($validated);

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Deactivate a company without deleting its records.
     */
    public function destroy(Company $company)
    {
        $company->update(['status' => 'inactive']);

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Company deactivated successfully.');
    }
}
