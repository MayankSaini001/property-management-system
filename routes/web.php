
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Http\Controllers\SuperAdmin\CompanyAdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    abort_unless(auth()->user()->isSuperAdmin(), 403);

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'can:isSuperAdmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::resource('companies', CompanyController::class)
            ->except(['show']);
    });

Route::middleware(['auth', 'verified', 'can:isSuperAdmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::resource('company-admins', CompanyAdminController::class)
            ->only(['index', 'create', 'store']);
    });

require __DIR__.'/auth.php';
