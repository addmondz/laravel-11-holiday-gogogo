<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PackageAddOnController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\TravelCalculatorController;
use App\Http\Controllers\ConfigurationPriceController;
use App\Http\Controllers\DateTypeController;
use App\Http\Controllers\SeasonTypeController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\DateTypeRangeController;
use App\Http\Controllers\PackageConfigurationController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Appli    cation::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::middleware('guest')->group(function () {
    Route::get('', [AuthenticatedSessionController::class, 'create'])->name('login');
});

Route::prefix('calculator')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Calculator');
    })->name('calculator');

    Route::prefix('api')->group(function () {
        Route::get('/get-resources', [TravelCalculatorController::class, 'getResources']);
        Route::get('/get-room-types/{packageId}', [TravelCalculatorController::class, 'getRoomTypes']);
        Route::post('/calculate-total', [TravelCalculatorController::class, 'calculate']);
    });
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('packages', PackageController::class)->names([
        'index' => 'packages.index',
        'create' => 'packages.create',
        'store' => 'packages.store',
        'show' => 'packages.show',
        'edit' => 'packages.edit',
        'update' => 'packages.update',
        'destroy' => 'packages.destroy'
    ]);


    // Route::resource('package-add-ons', PackageAddOnController::class)->names([
    //     'index' => 'package-add-ons.index',
    //     'create' => 'package-add-ons.create',
    //     'store' => 'package-add-ons.store',
    //     'show' => 'package-add-ons.show',
    //     'edit' => 'package-add-ons.edit',
    //     'update' => 'package-add-ons.update',
    //     'destroy' => 'package-add-ons.destroy'
    // ]);


    Route::resource('season-types', SeasonTypeController::class)->names([
        'index' => 'season-types.index',
        'create' => 'season-types.create',
        'store' => 'season-types.store',
        'show' => 'season-types.show',
        'edit' => 'season-types.edit',
        'update' => 'season-types.update',
        'destroy' => 'season-types.destroy'
    ]);


    Route::resource('seasons', SeasonController::class)->names([
        'index' => 'seasons.index',
        'create' => 'seasons.create',
        'store' => 'seasons.store',
        'show' => 'seasons.show',
        'edit' => 'seasons.edit',
        'update' => 'seasons.update',
        'destroy' => 'seasons.destroy'
    ]);

    Route::resource('date-types', DateTypeController::class)->names([
        'index' => 'date-types.index',
        'create' => 'date-types.create',
        'store' => 'date-types.store',
        'show' => 'date-types.show',
        'edit' => 'date-types.edit',
        'update' => 'date-types.update',
        'destroy' => 'date-types.destroy'
    ]);

    Route::resource('date-type-ranges', DateTypeRangeController::class)->names([
        'index' => 'date-type-ranges.index',
        'create' => 'date-type-ranges.create',
        'store' => 'date-type-ranges.store',
        'show' => 'date-type-ranges.show',
        'edit' => 'date-type-ranges.edit',
        'update' => 'date-type-ranges.update',
        'destroy' => 'date-type-ranges.destroy'
    ]);

    Route::resource('package-configurations', PackageConfigurationController::class)->names([
        'index' => 'package-configurations.index',
        'create' => 'package-configurations.create',
        'store' => 'package-configurations.store',
        'show' => 'package-configurations.show',
        'edit' => 'package-configurations.edit',
        'update' => 'package-configurations.update',
        'destroy' => 'package-configurations.destroy'
    ]);

    Route::resource('configuration-prices', ConfigurationPriceController::class)->names([
        'index' => 'configuration-prices.index',
        'create' => 'configuration-prices.create',
        'store' => 'configuration-prices.store',
        'show' => 'configuration-prices.show',
        'edit' => 'configuration-prices.edit',
        'update' => 'configuration-prices.update',
        'destroy' => 'configuration-prices.destroy',
    ]);

    Route::post('configuration-prices/fetch-prices-search-index', [ConfigurationPriceController::class, 'fetchPricesSearchIndex'])
        ->name('configuration-prices.fetchPricesSearchIndex');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
