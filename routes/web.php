<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MicrosoftOAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyMasterController;
use App\Http\Controllers\ItemCategoryMasterController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemIssueController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\PurchReqController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\ProjectMasterController;
use App\Http\Controllers\PoolMasterController;
use App\Http\Controllers\CurrencyMasterController;
use App\Http\Controllers\SizeMasterController;
use App\Http\Controllers\SiteMasterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WarehouseMasterController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\PoolController as ApiPoolController;
use App\Http\Controllers\Api\CurrencyController as ApiCurrencyController;

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
    Route::get('/masters/pools', [PoolMasterController::class, 'index'])
        ->name('masters.pools.index');
    Route::get('/masters/currencies', [CurrencyMasterController::class, 'index'])
        ->name('masters.currencies.index');
    Route::get('/masters/site', [SiteMasterController::class, 'index'])
        ->name('masters.site.index');
    Route::get('/masters/sizes', [SizeMasterController::class, 'index'])
        ->name('masters.sizes.index');
    Route::get('/masters/categories', [ItemCategoryMasterController::class, 'index'])
        ->name('masters.categories.index');
    Route::post('/masters/categories', [ItemCategoryMasterController::class, 'store'])
        ->name('masters.categories.store');
    Route::get('/masters/items', [ItemMasterController::class, 'index'])
        ->name('masters.items.index');
    Route::post('/masters/items', [ItemMasterController::class, 'store'])
        ->name('masters.items.store');

    $masterStubs = [
        'colors' => 'Colors',
        'styles' => 'Styles',
        'locations' => 'Locations',
        'units' => 'Units',
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
    Route::get('/masters/warehouses', [WarehouseMasterController::class, 'index'])
        ->name('masters.warehouses.index');
    Route::post('/masters/warehouses', [WarehouseMasterController::class, 'store'])
        ->name('masters.warehouses.store');
    Route::delete('/masters/warehouses/{warehouse}', [WarehouseMasterController::class, 'destroy'])
        ->name('masters.warehouses.destroy');
    // Web-authenticated JSON endpoints for master screens (no browser bearer token needed)
    Route::prefix('/masters/api')->name('masters.api.')->group(function () {
        Route::get('/sizes', [SizeController::class, 'index'])->name('sizes.index');
        Route::post('/sizes', [SizeController::class, 'store'])->name('sizes.store');
        Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('sizes.destroy');
        Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
        Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
        Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
        Route::get('/pools', [ApiPoolController::class, 'index'])->name('pools.index');
        Route::post('/pools', [ApiPoolController::class, 'store'])->name('pools.store');
        Route::delete('/pools/{pool}', [ApiPoolController::class, 'destroy'])->name('pools.destroy');
        Route::get('/currencies', [ApiCurrencyController::class, 'index'])->name('currencies.index');
        Route::post('/currencies', [ApiCurrencyController::class, 'store'])->name('currencies.store');
        Route::delete('/currencies/{currency}', [ApiCurrencyController::class, 'destroy'])->name('currencies.destroy');
    });

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
    Route::get('/modules/project-management/item-issue/api/journals/{journal}', [ItemIssueController::class, 'showJournal'])
        ->name('modules.project-management.item-issue.api.journals.show');
    Route::delete('/modules/project-management/item-issue/api/journals/{journal}', [ItemIssueController::class, 'destroyJournal'])
        ->name('modules.project-management.item-issue.api.journals.destroy');

    // Settings
    Route::get('/settings', fn() => redirect()->route('settings.token'))->name('settings.index');
    Route::get('/settings/token', [SettingsController::class, 'tokenIndex'])->name('settings.token');
    Route::post('/settings/token/generate', [SettingsController::class, 'generateToken'])->name('settings.token.generate');
    Route::get('/settings/credentials', [SettingsController::class, 'credsIndex'])->name('settings.credentials');
    Route::post('/settings/credentials', [SettingsController::class, 'saveCredentials'])->name('settings.credentials.save');
    Route::get('/settings/roles-permissions', [SettingsController::class, 'rolesPermissionsIndex'])->name('settings.roles-permissions');

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
    Route::post('/modules/procurement/purch-req/api/save', [PurchReqController::class, 'saveDraft'])
        ->name('modules.procurement.purch-req.save');
    Route::put('/modules/procurement/purch-req/api/drafts/{journal}', [PurchReqController::class, 'updateDraft'])
        ->name('modules.procurement.purch-req.drafts.update');
    Route::get('/modules/procurement/purch-req/api/journals/{journal}', [PurchReqController::class, 'showJournal'])
        ->name('modules.procurement.purch-req.journals.show');
    Route::delete('/modules/procurement/purch-req/api/journals/{journal}', [PurchReqController::class, 'destroyJournal'])
        ->name('modules.procurement.purch-req.journals.destroy');
    Route::post('/modules/procurement/purch-req/api/units', [PurchReqController::class, 'lookupUnits'])
        ->name('modules.procurement.purch-req.api.units');
    Route::post('/modules/procurement/purch-req/api/catalog', [PurchReqController::class, 'lookupCatalog'])
        ->name('modules.procurement.purch-req.api.catalog');
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
    Route::get('/modules/procurement/grn', [GrnController::class, 'index'])
        ->name('modules.procurement.grn');
    Route::get('/modules/procurement/grn/view', [GrnController::class, 'view'])
        ->name('modules.procurement.grn.view');
    Route::post('/modules/procurement/grn/api/search', [GrnController::class, 'search'])
        ->name('modules.procurement.grn.api.search');
    Route::post('/modules/procurement/grn/api/lines', [GrnController::class, 'lineDetails'])
        ->name('modules.procurement.grn.api.lines');
    Route::post('/modules/procurement/grn/api/post', [GrnController::class, 'postPackingSlip'])
        ->name('modules.procurement.grn.api.post');
    
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