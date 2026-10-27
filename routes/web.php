<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\CompanyAdminController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\PropertyController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified', 'active.company'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return view('dashboard');
        }

        return view('company.dashboard', ['company' => $user->company]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'can:isSuperAdmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::resource('company-admins', CompanyAdminController::class)
            ->only(['index', 'create', 'store']);
    });

Route::middleware(['auth', 'verified', 'active.company', 'can:isCompanyAdmin'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {
        Route::resource('properties', PropertyController::class)
            ->except(['show', 'destroy']);

        Route::patch('properties/{property}/status', [PropertyController::class, 'toggleStatus'])
            ->name('properties.status');
    });

require __DIR__ . '/auth.php';