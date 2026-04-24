<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MicrosoftOAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyMasterController;
use App\Http\Controllers\ItemIssueController;
use App\Http\Controllers\PurchReqController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\ProjectMasterController;
use App\Http\Controllers\SettingsController;

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

// Auth middleware expects a named "login" route
Route::get('/login', function () {
    return redirect()->route('home');
})->name('login');

// Microsoft OAuth Routes
Route::get('/auth/microsoft', [MicrosoftOAuthController::class, 'redirectToMicrosoft'])
    ->name('auth.microsoft');

Route::get('/auth/microsoft/callback', [MicrosoftOAuthController::class, 'handleMicrosoftCallback'])
    ->name('auth.microsoft.callback');

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

    // Company Master (first master setup)
    Route::get('/masters/company', [CompanyMasterController::class, 'index'])
        ->name('masters.company.index');
    Route::post('/masters/company', [CompanyMasterController::class, 'store'])
        ->name('masters.company.store');
    Route::post('/masters/company/quick-add', [CompanyMasterController::class, 'quickStore'])
        ->name('masters.company.quick-add');
    Route::post('/masters/company/sync-d365', [CompanyMasterController::class, 'syncFromD365'])
        ->name('masters.company.sync');

    Route::get('/masters/project', [ProjectMasterController::class, 'index'])
        ->name('masters.project.index');
    Route::post('/masters/project/quick-add', [ProjectMasterController::class, 'quickStore'])
        ->name('masters.project.quick-add');
    Route::post('/masters/project/sync-d365', [ProjectMasterController::class, 'syncFromD365'])
        ->name('masters.project.sync');

    $masterStubs = [
        'categories' => 'Categories',
        'items' => 'Items',
        'sizes' => 'Sizes',
        'colors' => 'Colors',
        'styles' => 'Styles',
        'locations' => 'Locations',
        'sites' => 'Sites',
        'warehouses' => 'Warehouses',
        'currencies' => 'Currencies',
        'units' => 'Units',
        'pools' => 'Pools',
        'batches' => 'Batches',
        'sales-tax-groups' => 'Sales Tax Groups',
        'item-sales-tax-groups' => 'Item Sales Tax Groups',
        'department-managers' => 'Department Managers',
    ];
    foreach ($masterStubs as $slug => $title) {
        Route::get("/masters/{$slug}", function () use ($title) {
            return view('masters.placeholder', ['title' => $title]);
        })->name("masters.{$slug}.index");
    }

    Route::get('/modules/project-management/item-issue', [ItemIssueController::class, 'index'])
        ->name('modules.project-management.item-issue');
    Route::post('/modules/project-management/item-issue/api/items/lookup', [ItemIssueController::class, 'lookupItems'])
        ->name('modules.project-management.item-issue.api.items.lookup');
    Route::post('/modules/project-management/item-issue/api/projects/lookup', [ItemIssueController::class, 'lookupProjects'])
        ->name('modules.project-management.item-issue.api.projects.lookup');
    Route::post('/modules/project-management/item-issue/api/post', [ItemIssueController::class, 'post'])
        ->name('modules.project-management.item-issue.api.post');
    Route::post('/modules/project-management/item-issue/api/onhand', [ItemIssueController::class, 'lookupOnHand'])
        ->name('modules.project-management.item-issue.api.onhand');
    Route::post('/modules/project-management/item-issue/api/units', [ItemIssueController::class, 'lookupUnits'])
        ->name('modules.project-management.item-issue.api.units');

    // Settings
    Route::get('/settings', fn() => redirect()->route('settings.token'))->name('settings.index');
    Route::get('/settings/token', [SettingsController::class, 'tokenIndex'])->name('settings.token');
    Route::post('/settings/token/generate', [SettingsController::class, 'generateToken'])->name('settings.token.generate');
    Route::get('/settings/credentials', [SettingsController::class, 'credsIndex'])->name('settings.credentials');
    Route::post('/settings/credentials', [SettingsController::class, 'saveCredentials'])->name('settings.credentials.save');

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
    Route::get('/purchase-requisitions', [PurchaseRequisitionController::class, 'index'])
        ->name('purchase-requisitions.index');
    Route::post('/purchase-requisitions/api/post', [PurchaseRequisitionController::class, 'post'])
        ->name('purchase-requisitions.api.post');
    Route::get('/modules/procurement/purch-req', [PurchReqController::class, 'index'])
        ->name('modules.procurement.purch-req');
    Route::post('/modules/procurement/purch-req/api/post', [PurchReqController::class, 'post'])
        ->name('modules.procurement.purch-req.post');
    Route::post('/modules/procurement/purch-req/api/units', [PurchReqController::class, 'lookupUnits'])
        ->name('modules.procurement.purch-req.api.units');
    Route::get('/modules/procurement/purch-req/{journal}/attachments/{index}', [PurchReqController::class, 'downloadAttachment'])
        ->name('modules.procurement.purch-req.attachment')
        ->where('index', '[0-9]+');
    Route::get('/modules/procurement/purch-req/{journal}/attachments/{index}/base64', [PurchReqController::class, 'viewBase64'])
        ->name('modules.procurement.purch-req.attachment.base64')
        ->where('index', '[0-9]+');
    
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