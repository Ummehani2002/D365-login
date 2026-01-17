<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MicrosoftOAuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home/Login Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Microsoft OAuth Routes
Route::get('/auth/microsoft', [MicrosoftOAuthController::class, 'redirectToMicrosoft'])
    ->name('auth.microsoft');

Route::get('/auth/microsoft/callback', [MicrosoftOAuthController::class, 'handleMicrosoftCallback']);

// Logout
Route::post('/logout', [MicrosoftOAuthController::class, 'logout'])
    ->name('logout');

// ===========================================
// PROTECTED ROUTES (Require Authentication)
// ===========================================
Route::middleware(['auth'])->group(function () {
    
    // Dashboard (Main protected route)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    // Laravel expects /home after login, so redirect it to dashboard
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    });
    
    // ===========================================
    // MASTER MODULES
    // ===========================================
    
    // Quotation Module
    Route::get('/quotations', function () {
        return "Quotation Module - Coming Soon";
    })->name('quotations.index');
    
    // Purchase Requisition Module
    Route::get('/purchase-requisitions', function () {
        return "Purchase Requisition Module - Coming Soon";
    })->name('purchase-requisitions.index');
    
    // Purchase Order Module
    Route::get('/purchase-orders', function () {
        return "Purchase Order Module - Coming Soon";
    })->name('purchase-orders.index');
    
    // Goods Receive Note (GRN) Module
    Route::get('/grns', function () {
        return "GRN Module - Coming Soon";
    })->name('grns.index');
    
    // Inventory Module
    Route::get('/inventory', function () {
        return "Inventory Module - Coming Soon";
    })->name('inventory.index');
    
    // Vendors/Suppliers Module
    Route::get('/vendors', function () {
        return "Vendors Module - Coming Soon";
    })->name('vendors.index');
    
    // Customers Module
    Route::get('/customers', function () {
        return "Customers Module - Coming Soon";
    })->name('customers.index');
    
    // Reports Module
    Route::get('/reports', function () {
        return "Reports Module - Coming Soon";
    })->name('reports.index');
});

// ===========================================
// DEBUG ROUTES (Remove in production)
// ===========================================
Route::get('/debug-config', function() {
    $config = [
        'app_url' => env('APP_URL'),
        'client_id_set' => !empty(env('MICROSOFT_CLIENT_ID')),
        'client_secret_set' => !empty(env('MICROSOFT_CLIENT_SECRET')),
        'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
        'tenant_id_valid' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', env('MICROSOFT_TENANT_ID')),
        'status' => 'READY',
    ];
    
    return response()->json($config);
});

Route::get('/debug-auth', function() {
    return [
        'authenticated' => auth()->check(),
        'user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ] : null,
        'intended_url' => session()->get('url.intended'),
        'session_data' => session()->all()
    ];
});