<?php

use App\Http\Controllers\Company\GuestController;
use App\Http\Controllers\Company\InventoryBlockController;
use App\Http\Controllers\Company\PropertyController;
use App\Http\Controllers\Company\RoomTypeController;
use App\Http\Controllers\Company\UnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\CompanyAdminController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Support\Facades\Route;

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

        // Properties
        Route::resource('properties', PropertyController::class)
            ->except(['show', 'destroy']);

        Route::patch('properties/{property}/status', [PropertyController::class, 'toggleStatus'])
            ->name('properties.status');

        // Room types (hotels only)
        Route::get('properties/{property}/room-types', [RoomTypeController::class, 'index'])
            ->name('room-types.index');
        Route::get('properties/{property}/room-types/create', [RoomTypeController::class, 'create'])
            ->name('room-types.create');
        Route::post('properties/{property}/room-types', [RoomTypeController::class, 'store'])
            ->name('room-types.store');

        Route::get('room-types/{roomType}/edit', [RoomTypeController::class, 'edit'])
            ->name('room-types.edit');
        Route::put('room-types/{roomType}', [RoomTypeController::class, 'update'])
            ->name('room-types.update');
        Route::patch('room-types/{roomType}/status', [RoomTypeController::class, 'toggleStatus'])
            ->name('room-types.status');

        // Units: hotel rooms and villa units
        Route::get('properties/{property}/units', [UnitController::class, 'index'])
            ->name('units.index');
        Route::get('properties/{property}/units/create', [UnitController::class, 'create'])
            ->name('units.create');
        Route::post('properties/{property}/units', [UnitController::class, 'store'])
            ->name('units.store');

        Route::get('units/{unit}/edit', [UnitController::class, 'edit'])
            ->name('units.edit');
        Route::put('units/{unit}', [UnitController::class, 'update'])
            ->name('units.update');
        Route::patch('units/{unit}/status', [UnitController::class, 'updateStatus'])
            ->name('units.status');

        // Maintenance and owner-use blocks
        Route::get('properties/{property}/blocks', [InventoryBlockController::class, 'index'])
            ->name('blocks.index');
        Route::get('properties/{property}/blocks/create', [InventoryBlockController::class, 'create'])
            ->name('blocks.create');
        Route::post('properties/{property}/blocks', [InventoryBlockController::class, 'store'])
            ->name('blocks.store');

        Route::delete('blocks/{block}', [InventoryBlockController::class, 'destroy'])
            ->name('blocks.destroy');

        // Guests
        Route::resource('guests', GuestController::class)
            ->except(['destroy']);
    });

require __DIR__ . '/auth.php';