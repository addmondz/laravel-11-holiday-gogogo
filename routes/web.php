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
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentSimulationController;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\BotApiController;
use App\Http\Controllers\DateBlockerController;
use App\Http\Controllers\SenangPayController;
use App\Models\Transaction;
use App\Models\Package;

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

Route::prefix('quotation')->group(function () {
    Route::prefix('api')->group(function () {
        Route::post('/fetch-package-by-uuid', [TravelCalculatorController::class, 'fetchPackageByUuid'])->name('api.fetch-package-by-uuid');
        Route::post('/package-calculate-price', [TravelCalculatorController::class, 'packageCalculatePrice'])->name('api.package-calculate-price');
        Route::post('/bookings', [ApiBookingController::class, 'store'])->name('api.bookings.store');
    });

    Route::get('/fetch/{uuid}', function ($uuid, Request $request) {
        $booking = null;
        if ($request->has('booking')) {
            $booking = Booking::where('uuid', $request->booking)->with('rooms.roomType')->first();
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
    Route::post('/seasons/store-bulk', [SeasonController::class, 'storeBulk'])->name('seasons.store-bulk');

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
    Route::post('/room-types/{roomType}/duplicate', [RoomTypeController::class, 'duplicate'])->name('room-types.duplicate');

    Route::resource('date-type-ranges', DateTypeRangeController::class)->names([
        'index' => 'date-type-ranges.index',
        'create' => 'date-type-ranges.create',
        'store' => 'date-type-ranges.store',
        'show' => 'date-type-ranges.show',
        'edit' => 'date-type-ranges.edit',
        'update' => 'date-type-ranges.update',
        'destroy' => 'date-type-ranges.destroy'
    ]);
    Route::post('/date-type-ranges/store-bulk', [DateTypeRangeController::class, 'storeBulk'])->name('date-type-ranges.store-bulk');

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
        Route::post('/fetch-prices-room-types', 'fetchPricesRoomTypes')->name('configuration-prices.fetchPricesRoomTypes');
        Route::put('/update-room-type-prices', 'updateRoomTypePrices')->name('configuration-prices.updateRoomTypePrices');
        Route::post('/create-price-configuration', 'createPriceConfiguration')->name('configuration-prices.createPriceConfiguration');
        Route::post('/duplicate-to-multiple', 'duplicateToMultiple')->name('configuration-prices.duplicateToMultiple');
    });

    Route::resource('bookings', BookingController::class)->names([
        'index' => 'bookings.index',
        'show' => 'bookings.show',
        'edit' => 'bookings.edit',
        'update' => 'bookings.update'
    ]);

    // Booking approval routes
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

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

    // Package Add-ons routes
    Route::prefix('package-add-ons')->controller(\App\Http\Controllers\PackageAddOnController::class)->group(function () {
        Route::get('/{package}', 'index')->name('package-add-ons.index');
        Route::post('/{package}', 'store')->name('package-add-ons.store');
        Route::put('/{packageAddOn}', 'update')->name('package-add-ons.update');
        Route::delete('/{packageAddOn}', 'destroy')->name('package-add-ons.destroy');
    });

    // settings routes
    Route::resource('settings', AppSettingController::class)->names([
        'index' => 'settings.index',
        'edit' => 'settings.edit',
        'update' => 'settings.update',
    ]);

    // SST Configuration routes
    Route::prefix('settings')->controller(AppSettingController::class)->group(function () {
        Route::get('/sst-configuration', 'getSstConfiguration')->name('settings.sst-configuration.get');
        Route::post('/sst-configuration', 'updateSstConfiguration')->name('settings.sst-configuration.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// Payment Routes
Route::get('/payment/return', [SenangPayController::class, 'handleReturn']);
Route::post('/payment/callback', [SenangPayController::class, 'handleCallback']);
Route::post('/payment/initiate/{bookingId}', [SenangPayController::class, 'initiatePayment'])->name('payment.initiate');
// payment testing
Route::get('/test-payment', [PaymentSimulationController::class, 'showTestPayment'])->name('payment.test-payment');
Route::post('/create-test-payment-transaction', [PaymentSimulationController::class, 'createTestPaymentTransaction'])->name('payment.create-test-payment-transaction');

// Bot API Routes
Route::prefix('bot-api')->group(function () {
    Route::post('/fetch-room-types', [BotApiController::class, 'fetchRoomTypesByPackageName']);
    Route::post('/fetch-quotation', [BotApiController::class, 'fetchQuotation']);
    Route::get('/docs', function () {
        $firstPackage = Package::first();
        $metadata = ['baseUrl' => url('/'), 'botPrefix' => 'bot-api', 'firstPackageName' => $firstPackage->name, 'packageRoomsIds' => $firstPackage->loadRoomTypes->pluck('id')];
        return view('api-docs', $metadata);
    })->name('bot-api.docs');
});


use Illuminate\Support\Facades\DB;
Route::get('/show-duplicate-price-configuration', function () {
    // Subquery: duplicate keys (the combos appearing > 1 time)
    $dupKeys = DB::table('package_configurations')
        ->select('package_id','season_type_id','date_type_id','room_type_id', DB::raw('COUNT(*) as c'))
        ->groupBy('package_id','season_type_id','date_type_id','room_type_id')
        ->havingRaw('COUNT(*) > 1');

    // Join back to get all duplicate rows
    $rows = DB::table('package_configurations as pc')
        ->joinSub($dupKeys, 'dup', function ($join) {
            $join->on('pc.package_id','=','dup.package_id')
                 ->on('pc.season_type_id','=','dup.season_type_id')
                 ->on('pc.date_type_id','=','dup.date_type_id')
                 ->on('pc.room_type_id','=','dup.room_type_id');
        })
        ->orderBy('pc.package_id')
        ->orderBy('pc.season_type_id')
        ->orderBy('pc.date_type_id')
        ->orderBy('pc.room_type_id')
        ->get();

    // Optional: just the keys & counts
    $keys = $dupKeys->get();

    return response()->json([
        'duplicate_keys' => $keys,   // each item has c = count
        // 'duplicate_rows' => $rows,   // the actual rows involved
    ]);
});
