<?php

use App\Http\Controllers\Api\BookingController as ApiBookingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PackageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\TravelCalculatorController;
use App\Http\Controllers\ConfigurationPriceController;
use App\Http\Controllers\DateTypeController;
use App\Http\Controllers\SeasonTypeController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\DateTypeRangeController;
use App\Http\Controllers\PackageConfigurationController;
use App\Http\Controllers\RoomTypeController;
use App\Models\DateType;
use App\Models\DateTypeRange;
use App\Models\PackageConfiguration;
use App\Models\RoomType;
use App\Models\Season;
use App\Models\SeasonType;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentSimulationController;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\DateBlockerController;

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
    // Route::get('/', function () {
    //     return Inertia::render('Calculator');
    // })->name('calculator');

    Route::prefix('api')->group(function () {
        // Route::get('/get-resources', [TravelCalculatorController::class, 'getResources']);
        // Route::get('/get-room-types/{packageId}', [TravelCalculatorController::class, 'getRoomTypes']);
        // Route::post('/calculate-total', [TravelCalculatorController::class, 'calculate']);
        Route::post('/fetch-package-by-uuid', [TravelCalculatorController::class, 'fetchPackageByUuid'])->name('api.fetch-package-by-uuid');
        Route::post('/package-calculate-price', [TravelCalculatorController::class, 'packageCalculatePrice'])->name('api.package-calculate-price');
        Route::post('/bookings', [ApiBookingController::class, 'store'])->name('api.bookings.store');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('api.transactions.store');
        
        // Payment routes
        Route::prefix('bookings/{uuid}/payment')->name('api.payment.')->group(function ($uuid) {
            Route::get('/', [PaymentController::class, 'show'])->name('show');
            Route::post('/', [PaymentController::class, 'handlePayment'])->name('handle');
            Route::get('/success', [PaymentController::class, 'success'])->name('success');
            Route::get('/failed', [PaymentController::class, 'failed'])->name('failed');
        });
    });

    Route::get('/quotation/{uuid}', function ($uuid, Request $request) {
        $booking = null;
        if ($request->has('booking')) {
            $booking = Booking::where('uuid', $request->booking)->with('roomType')->first();
        }
        return Inertia::render('Quotation/WithHash', [
            'uuid' => $uuid,
            'booking' => $booking
        ]);
    })->name('quotation.with-hash');
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

    Route::get('/packages/{package}/duplicate', [PackageController::class, 'duplicateForm'])->name('packages.duplicate-form');
    Route::post('/packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');

    Route::get('/packages/{package}/room-types', [PackageController::class, 'getRoomTypes'])->name('packages.room-types');
    Route::get('/packages/{package}/seasons', [PackageController::class, 'getSeasons'])->name('packages.seasons');
    Route::get('/packages/{package}/date-type-ranges', [PackageController::class, 'getDateTypeRanges'])->name('packages.date-type-ranges');

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

    Route::resource('room-types', RoomTypeController::class)->names([
        'create' => 'room-types.create',
        'edit' => 'room-types.edit',
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

    Route::prefix('configuration-prices')->controller(ConfigurationPriceController::class)->group(function () {
        Route::post('fetch-prices-search-index', 'fetchPricesSearchIndex')->name('configuration-prices.fetchPricesSearchIndex');
        Route::put('update', 'updatePrices')->name('configuration-prices.updatePrices');
        Route::post('store', 'store')->name('configuration-prices.store');
        Route::post('/fetch-prices-room-types', 'fetchPricesRoomTypes')->name('configuration-prices.fetchPricesRoomTypes');
        Route::put('/update-room-type-prices', 'updateRoomTypePrices')->name('configuration-prices.updateRoomTypePrices');
    });

    Route::resource('bookings', BookingController::class)->names([
        'index' => 'bookings.index',
        'show' => 'bookings.show',
        'edit' => 'bookings.edit',
        'update' => 'bookings.update'
    ]);

    Route::resource('users', UserController::class)->names([
        'index' => 'users.index',
        'edit' => 'users.edit',
        'update' => 'users.update',
        'destroy' => 'users.destroy',
        'create' => 'users.create',
        'store' => 'users.store'
    ])->only(['index', 'edit', 'update', 'destroy', 'create', 'store']);

    Route::prefix('date-blockers')->controller(DateBlockerController::class)->group(function () {
        Route::get('/', 'index')->name('date-blockers.index');
        Route::post('/', 'store')->name('date-blockers.store');
        Route::put('/{dateBlocker}', 'update')->name('date-blockers.update');
        Route::delete('/{dateBlocker}', 'destroy')->name('date-blockers.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';


Route::get('/test', function () {
    $package_id = 1;

    $season_name = 'Default';
    $season_type_id = SeasonType::where('name', $season_name)->first()?->id;
    $season_id = Season::where('season_type_id', $season_type_id)->where('package_id', $package_id)->first()?->id;

    $date_type_name = 'Weekday';
    $date_type_id = DateType::where('name', $date_type_name)->first()?->id;
    $date_type_range_id = DateTypeRange::where('date_type_id', $date_type_id)->where('package_id', $package_id)->first()?->id;

    $room_type_name = 'Deluxe Room';
    $room_type_id = RoomType::where('name', $room_type_name)->first()?->id;

    dump([
        'season_name' => $season_name,
        'season_type_id' => $season_type_id,
        'season_id' => $season_id,
        'date_type_name' => $date_type_name,
        'date_type_id' => $date_type_id,
        'room_type_name' => $room_type_name,
        'room_type_id' => $room_type_id,
    ]);

    $package_config = PackageConfiguration::where('package_id', $package_id)
        ->where('season_id', $season_id)
        ->where('date_type_id', $date_type_range_id)
        ->where('room_type_id', $room_type_id)
        ->first();

    dump('package_config id: ' . $package_config?->id);

    $configurationPrices = $package_config ? json_decode($package_config->configuration_prices, true) : [];

    dd($configurationPrices ?: 'No prices found.');
});

// Payment Simulation Routes
Route::get('/payment/{transaction}/simulate', [PaymentSimulationController::class, 'show'])->name('payment.simulate');
