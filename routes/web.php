<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CarbonCreditController;
use App\Http\Controllers\DeviceController;


Route::get('/', function () {
    return view('welcome');
});

// Auth routes (Using Laravel Breeze/UI)
require __DIR__ . '/auth.php';


Route::middleware(['auth'])->group(function () {

    // ... SEMUA ROUTE KAMU YANG SUDAH ADA

    // ================================
    // ADMIN – DAFTAR PENGGUNA
    // ================================
    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->name('admin.users.index')
        ->middleware('admin');
});


/*
|--------------------------------------------------------------------------
| ROUTES DALAM AUTH
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /* ================================
     * USER & UMUM
     * ================================ */

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Emission Monitoring
    Route::get('/emission-monitoring', [DashboardController::class, 'emissionMonitoring'])
        ->name('emission.monitoring');
    Route::get('/api/emission-data', [DashboardController::class, 'getEmissionData'])
        ->name('api.emission.data');

    // Daftar kendaraan (vehicles)
    Route::get('/carbon-credits/vehicles', [CarbonCreditController::class, 'vehicles'])
        ->name('carbon-credits.vehicles');

    /* ================================
     * USER – AJUKAN JUAL / BELI
     * ================================ */

    // FORM JUAL
    Route::get(
        '/carbon-credits/{carbonCredit}/request-sale',
        [CarbonCreditController::class, 'requestSale']
    )->name('carbon-credits.request-sale');

    // SUBMIT JUMLAH YANG DIJUAL (FORM JUAL)
    Route::post(
        '/carbon-credits/{carbonCredit}/submit-sale',
        [CarbonCreditController::class, 'submitSaleRequest']
    )->name('carbon-credits.submit-sale-request');

    // FORM BELI
    Route::get(
        '/carbon-credits/{carbonCredit}/request-buy',
        [CarbonCreditController::class, 'requestBuy']
    )->name('carbon-credits.request-buy');

    // SUBMIT JUMLAH YANG DIBELI (FORM BELI)
    Route::post(
        '/carbon-credits/{carbonCredit}/submit-buy',
        [CarbonCreditController::class, 'submitBuyRequest']
    )->name('carbon-credits.submit-buy-request');

    /* ================================
     * ADMIN – PANEL & APPROVAL JUAL BELI
     * ================================ */

    // Admin trade panel
    Route::get(
        '/admin/trades',
        [CarbonCreditController::class, 'adminTradePanel']
    )->name('admin.trades.index')->middleware('admin');

    // Approve / Reject Sale
    Route::patch(
        '/carbon-credits/{carbonCredit}/approve-sale-request',
        [CarbonCreditController::class, 'approveSaleRequest']
    )->name('carbon-credits.approve-sale-request')->middleware('admin');

    Route::patch(
        '/carbon-credits/{carbonCredit}/reject-sale-request',
        [CarbonCreditController::class, 'rejectSaleRequest']
    )->name('carbon-credits.reject-sale-request')->middleware('admin');

    // Approve / Reject Buy
    Route::patch(
        '/carbon-credits/{carbonCredit}/approve-buy-request',
        [CarbonCreditController::class, 'approveBuyRequest']
    )->name('carbon-credits.approve-buy-request')->middleware('admin');

    Route::patch(
        '/carbon-credits/{carbonCredit}/reject-buy-request',
        [CarbonCreditController::class, 'rejectBuyRequest']
    )->name('carbon-credits.reject-buy-request')->middleware('admin');

    /* ================================
     * ADMIN – APPROVAL KENDARAAN
     * ================================ */

    Route::patch(
        '/carbon-credits/{carbonCredit}/approve',
        [CarbonCreditController::class, 'approve']
    )->name('carbon-credits.approve')->middleware('admin');

    Route::patch(
        '/carbon-credits/{carbonCredit}/reject',
        [CarbonCreditController::class, 'reject']
    )->name('carbon-credits.reject')->middleware('admin');

    Route::patch(
        '/carbon-credits/{carbonCredit}/set-available',
        [CarbonCreditController::class, 'setAvailable']
    )->name('carbon-credits.set-available')->middleware('admin');

    /* ================================
     * CARBON CREDITS RESOURCE
     * ================================ */
    Route::resource('carbon-credits', CarbonCreditController::class);

    /* ================================
     * DEVICE SENSOR (DEVICECONTROLLER)
     * ================================ */
    // List semua device
Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');

Route::get('/devices/create/{carbonCredit}', [DeviceController::class, 'create'])
    ->name('devices.create');
// Store device
Route::post('/devices/{carbonCredit}', [DeviceController::class, 'store'])->name('devices.store');

// Detail device
Route::get('/devices/{carbonCredit}', [DeviceController::class, 'show'])->name('devices.show');

// Edit device
Route::get('/devices/{carbonCredit}/edit', [DeviceController::class, 'edit'])->name('devices.edit');

// Update device
Route::patch('/devices/{carbonCredit}', [DeviceController::class, 'update'])->name('devices.update');

// Hapus device
Route::delete('/devices/{carbonCredit}', [DeviceController::class, 'destroy'])->name('devices.destroy');

// QR Code
Route::get('/devices/{carbonCredit}/qr-code', [DeviceController::class, 'generateQrCode'])->name('devices.qr-code');


    /* ================================
     * TRANSACTIONS & PAYOUTS
     * ================================ */

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/create/{carbonCredit}', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions/{carbonCredit}', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/payment', [TransactionController::class, 'showPayment'])->name('transactions.payment');

    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/{payout}', [PayoutController::class, 'show'])->name('payouts.show');
    Route::post('/payouts/{payout}/create', [PayoutController::class, 'create'])->name('payouts.create');
    Route::get('/payouts/{payout}/approve', [PayoutController::class, 'showApproveForm'])->name('payouts.approve.form');
    Route::post('/payouts/{payout}/approve', [PayoutController::class, 'approve'])->name('payouts.approve');

    // Admin payout
    Route::post('/payouts/{payout}/process', [PayoutController::class, 'process'])
        ->name('payouts.process')->middleware('admin');
    Route::post('/payouts/{payout}/check-status', [PayoutController::class, 'checkStatus'])
        ->name('payouts.check-status')->middleware('admin');
    Route::get('/admin/payouts', [PayoutController::class, 'adminIndex'])
        ->name('payouts.admin.index')->middleware('admin');
});

/*
|--------------------------------------------------------------------------
| ROUTES DI LUAR AUTH
|--------------------------------------------------------------------------
*/

// Notification URLs dari Midtrans
Route::post('/payment-notification', [TransactionController::class, 'handlePaymentNotification'])
    ->name('payment.notification');
Route::post('/payout-notification', [PayoutController::class, 'handlePayoutNotification'])
    ->name('payout.notification');

// Device setup route (untuk teknisi, tanpa auth)
Route::get(
    '/device-setup/{deviceId}',
    [DeviceController::class, 'setup']
)->name('devices.setup');

// API routes untuk webhook (tanpa CSRF protection)
Route::middleware('api')->group(function () {
    Route::post('/api/payment-callback', [TransactionController::class, 'handlePaymentNotification']);
    Route::post('/api/payout-callback', [PayoutController::class, 'handlePayoutNotification']);
});

// Logout manual
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');
