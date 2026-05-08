<?php

use App\Http\Controllers\Auth\MicrosoftOAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ─── Public / Auth ────────────────────────────────────────────────────────────

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/login', fn () => redirect()->route('home'))->name('login');
Route::get('/auth/microsoft', [MicrosoftOAuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft');
Route::get('/auth/microsoft/callback', [MicrosoftOAuthController::class, 'handleMicrosoftCallback'])->name('auth.microsoft.callback');
Route::post('/logout', [MicrosoftOAuthController::class, 'logout'])->name('logout');

// ─── Authenticated ────────────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', fn () => redirect()->route('dashboard'));

    // Placeholder module routes (coming soon)
    Route::get('/quotations',      fn () => 'Quotation Module - Coming Soon')->name('quotations.index');
    Route::get('/purchase-orders', fn () => 'Purchase Order Module - Coming Soon')->name('purchase-orders.index');
    Route::get('/inventory',       fn () => 'Inventory Module - Coming Soon')->name('inventory.index');
    Route::get('/vendors',         fn () => 'Vendors Module - Coming Soon')->name('vendors.index');
    Route::get('/customers',       fn () => 'Customers Module - Coming Soon')->name('customers.index');
    Route::get('/reports',         fn () => 'Reports Module - Coming Soon')->name('reports.index');
});

// ─── Grouped route files ──────────────────────────────────────────────────────

require __DIR__ . '/partials/masters.php';
require __DIR__ . '/partials/modules.php';
require __DIR__ . '/partials/settings.php';

// ─── Debug (remove in production) ────────────────────────────────────────────

Route::get('/debug-config', function () {
    return response()->json([
        'app_url'          => env('APP_URL'),
        'client_id_set'    => ! empty(env('MICROSOFT_CLIENT_ID')),
        'client_secret_set'=> ! empty(env('MICROSOFT_CLIENT_SECRET')),
        'redirect_uri'     => env('MICROSOFT_REDIRECT_URI'),
        'tenant_id'        => env('MICROSOFT_TENANT_ID'),
        'tenant_id_valid'  => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', env('MICROSOFT_TENANT_ID')),
    ]);
});
