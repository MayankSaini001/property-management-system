<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyAdminRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyAdminController extends Controller
{
    /**
     * Display all Company Admins.
     */
    public function index(): View
    {
        $admins = User::with('company')
            ->where('role', 'company_admin')
            ->latest()
            ->paginate(10);

        return view(
            'superadmin.company-admins.index',
            compact('admins')
        );
    }

    /**
     * Show the form to create a Company Admin.
     */
    public function create(): View
    {
        $companies = Company::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.company-admins.create',
            compact('companies')
        );
    }

    /**
     * Store a new Company Admin.
     */
    public function store(
        StoreCompanyAdminRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'company_admin',
            'company_id' => $validated['company_id'],
        ]);

        return redirect()
            ->route('superadmin.company-admins.index')
            ->with(
                'success',
                'Company Admin created successfully.'
            );
    }
}
