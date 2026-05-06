<?php

use App\Http\Controllers\Api\CurrencyController as ApiCurrencyController;
use App\Http\Controllers\Api\ItemSalesTaxGroupController as ApiItemSalesTaxGroupController;
use App\Http\Controllers\Api\ItemUnitController as ApiItemUnitController;
use App\Http\Controllers\Api\PoolController as ApiPoolController;
use App\Http\Controllers\Api\SalesTaxGroupController as ApiSalesTaxGroupController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Auth\MicrosoftOAuthController;
use App\Http\Controllers\CompanyMasterController;
use App\Http\Controllers\CurrencyMasterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\ItemCategoryMasterController;
use App\Http\Controllers\ItemIssueController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemSalesTaxGroupMasterController;
use App\Http\Controllers\ItemUnitMasterController;
use App\Http\Controllers\PoolMasterController;
use App\Http\Controllers\ProjectMasterController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\PurchReqController;
use App\Http\Controllers\RbacPermissionController;
use App\Http\Controllers\RbacRoleController;
use App\Http\Controllers\RbacUserController;
use App\Http\Controllers\RbacMenuMatchController;
use App\Http\Controllers\SalesTaxGroupMasterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteMasterController;
use App\Http\Controllers\SizeMasterController;
use App\Http\Controllers\WarehouseMasterController;
use App\Models\Company;
use Illuminate\Support\Facades\Route;

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

// Diagnostic endpoint to confirm which app is serving the current port.
Route::get('/__ping', function () {
    return response('laravel-web-app-main: ' . now()->toDateTimeString(), 200)
        ->header('Content-Type', 'text/plain');
})->name('ping');

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

    Route::middleware('permission:Master.access')->group(function () {
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
        Route::get('/masters/item-sales-tax-groups', [ItemSalesTaxGroupMasterController::class, 'index'])
            ->name('masters.item-sales-tax-groups.index');
        Route::get('/masters/sales-tax-groups', [SalesTaxGroupMasterController::class, 'index'])
            ->name('masters.sales-tax-groups.index');
        Route::get('/masters/units', [ItemUnitMasterController::class, 'index'])
            ->name('masters.units.index');

        $masterStubs = [
            'colors' => 'Colors',
            'styles' => 'Styles',
            'locations' => 'Locations',
            'batches' => 'Batches',
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
            // Singular alias (same as UI path /masters/site)
            Route::get('/site', [SiteController::class, 'index'])->name('site.index');
            Route::post('/site', [SiteController::class, 'store'])->name('site.store');
            Route::delete('/site/{site}', [SiteController::class, 'destroy'])->name('site.destroy');
            Route::get('/pools', [ApiPoolController::class, 'index'])->name('pools.index');
            Route::post('/pools', [ApiPoolController::class, 'store'])->name('pools.store');
            Route::delete('/pools/{pool}', [ApiPoolController::class, 'destroy'])->name('pools.destroy');
            Route::get('/currencies', [ApiCurrencyController::class, 'index'])->name('currencies.index');
            Route::post('/currencies', [ApiCurrencyController::class, 'store'])->name('currencies.store');
            Route::delete('/currencies/{currency}', [ApiCurrencyController::class, 'destroy'])->name('currencies.destroy');
            Route::get('/item-sales-tax-groups', [ApiItemSalesTaxGroupController::class, 'index'])->name('item-sales-tax-groups.index');
            Route::post('/item-sales-tax-groups', [ApiItemSalesTaxGroupController::class, 'store'])->name('item-sales-tax-groups.store');
            Route::delete('/item-sales-tax-groups/{item_sales_tax_group}', [ApiItemSalesTaxGroupController::class, 'destroy'])->name('item-sales-tax-groups.destroy');
            Route::get('/sales-tax-groups', [ApiSalesTaxGroupController::class, 'index'])->name('sales-tax-groups.index');
            Route::post('/sales-tax-groups', [ApiSalesTaxGroupController::class, 'store'])->name('sales-tax-groups.store');
            Route::delete('/sales-tax-groups/{sales_tax_group}', [ApiSalesTaxGroupController::class, 'destroy'])->name('sales-tax-groups.destroy');
            Route::get('/item-units', [ApiItemUnitController::class, 'index'])->name('item-units.index');
            Route::post('/item-units', [ApiItemUnitController::class, 'store'])->name('item-units.store');
            Route::delete('/item-units/{item_unit}', [ApiItemUnitController::class, 'destroy'])->name('item-units.destroy');
        });
    });

    // Settings
    Route::get('/settings', function () {
        $user = request()->user();

        if ($user?->isSuperAdmin()) {
            return redirect()->route('settings.token');
        }

        if ($user) {
            $companyCode = request()->query('company');
            if ($companyCode === null) {
                $companyCode = $user->accessibleCompanyD365Codes()->first();
            }

            $company = Company::resolveFromMixed($companyCode);
            if ($company && $user->hasPermissionForCompany($company, 'Super.access')) {
                return redirect()->route('settings.token');
            }
        }

        return redirect()->route('settings.users.index');
    })->name('settings.index');

    Route::middleware('super_admin')->group(function () {
        Route::get('/settings/token', [SettingsController::class, 'tokenIndex'])->name('settings.token');
        Route::post('/settings/token/generate', [SettingsController::class, 'generateToken'])->name('settings.token.generate');
        Route::get('/settings/credentials', [SettingsController::class, 'credsIndex'])->name('settings.credentials');
        Route::post('/settings/credentials', [SettingsController::class, 'saveCredentials'])->name('settings.credentials.save');
    });

    Route::middleware('permission:Settings.access')->group(function () {
        Route::get('/settings/users', [RbacUserController::class, 'index'])->name('settings.users.index');
        Route::prefix('/settings/users/api')->name('settings.users.api.')->group(function () {
            Route::get('/memberships', [RbacUserController::class, 'listMemberships'])->name('memberships.index');
            Route::post('/memberships', [RbacUserController::class, 'storeMembership'])->name('memberships.store');
            Route::match(['put', 'patch'], '/memberships/{membership}', [RbacUserController::class, 'updateMembership'])->name('memberships.update');
            Route::delete('/memberships/{membership}', [RbacUserController::class, 'destroyMembership'])->name('memberships.destroy');
            Route::get('/companies', [RbacUserController::class, 'listCompanies'])->name('companies.index');
            Route::get('/roles', [RbacUserController::class, 'rolesForCompany'])->name('roles.index');
            Route::get('/memberships/{membership}/role-scopes', [RbacUserController::class, 'roleScopes'])->name('memberships.role-scopes.index');
            Route::match(['post', 'put'], '/memberships/{membership}/role-scopes', [RbacUserController::class, 'upsertRoleScope'])->name('memberships.role-scopes.upsert');
        });
    });

    Route::middleware('permission:Settings.access')->group(function () {
        Route::get('/settings/roles', [RbacRoleController::class, 'index'])->name('settings.roles.index');
        Route::prefix('/settings/roles/api')->name('settings.roles.api.')->group(function () {
            Route::get('/roles', [RbacRoleController::class, 'listRoles'])->name('roles.index');
            Route::post('/roles', [RbacRoleController::class, 'store'])->name('roles.store');
            Route::match(['put', 'patch'], '/roles/{role}', [RbacRoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [RbacRoleController::class, 'destroy'])->name('roles.destroy');
            Route::get('/permissions', [RbacRoleController::class, 'listPermissions'])->name('permissions.index');
        });
    });

    Route::middleware('permission:Settings.access')->group(function () {
        Route::get('/settings/permissions', [RbacPermissionController::class, 'index'])->name('settings.permissions.index');
        Route::prefix('/settings/permissions/api')->name('settings.permissions.api.')->group(function () {
            Route::get('/permissions', [RbacPermissionController::class, 'listPermissions'])->name('permissions.index');
            Route::post('/permissions', [RbacPermissionController::class, 'store'])->name('permissions.store');
            Route::match(['put', 'patch'], '/permissions/{permission}', [RbacPermissionController::class, 'update'])->name('permissions.update');
            Route::delete('/permissions/{permission}', [RbacPermissionController::class, 'destroy'])->name('permissions.destroy');
        });
    });

    Route::middleware('permission:Settings.access')->group(function () {
        Route::get('/settings/menu-match', [RbacMenuMatchController::class, 'index'])->name('settings.menu-match.index');
        Route::prefix('/settings/menu-match/api')->name('settings.menu-match.api.')->group(function () {
            Route::get('/mappings', [RbacMenuMatchController::class, 'listMappings'])->name('mappings.index');
            Route::put('/mappings', [RbacMenuMatchController::class, 'updateMappings'])->name('mappings.update');
            Route::get('/available-menus', [RbacMenuMatchController::class, 'listAvailableMenus'])->name('available-menus.index');
            Route::post('/assign', [RbacMenuMatchController::class, 'assignMapping'])->name('assign.store');
        });
    });

    // Alias endpoints in /settings/rbac/* to support cleaner REST style without breaking existing route() names.
    Route::middleware('permission:Settings.access')->prefix('/settings/rbac/users')->name('settings.rbac.users.')->group(function () {
        Route::get('/', [RbacUserController::class, 'index'])->name('index');
        Route::get('/memberships', [RbacUserController::class, 'listMemberships'])->name('memberships.index');
        Route::post('/memberships', [RbacUserController::class, 'storeMembership'])->name('memberships.store');
        Route::match(['put', 'patch'], '/memberships/{membership}', [RbacUserController::class, 'updateMembership'])->name('memberships.update');
        Route::delete('/memberships/{membership}', [RbacUserController::class, 'destroyMembership'])->name('memberships.destroy');
        Route::get('/companies', [RbacUserController::class, 'listCompanies'])->name('companies.index');
        Route::get('/roles', [RbacUserController::class, 'rolesForCompany'])->name('roles.index');
        Route::get('/memberships/{membership}/role-scopes', [RbacUserController::class, 'roleScopes'])->name('memberships.role-scopes.index');
        Route::match(['post', 'put'], '/memberships/{membership}/role-scopes', [RbacUserController::class, 'upsertRoleScope'])->name('memberships.role-scopes.upsert');
    });

    Route::middleware('permission:Settings.access')->prefix('/settings/rbac/roles')->name('settings.rbac.roles.')->group(function () {
        Route::get('/', [RbacRoleController::class, 'index'])->name('index');
        Route::get('/list', [RbacRoleController::class, 'listRoles'])->name('list');
        Route::get('/permissions', [RbacRoleController::class, 'listPermissions'])->name('permissions');
        Route::post('/', [RbacRoleController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{role}', [RbacRoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RbacRoleController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:Settings.access')->prefix('/settings/rbac/permissions')->name('settings.rbac.permissions.')->group(function () {
        Route::get('/', [RbacPermissionController::class, 'index'])->name('index');
        Route::get('/list', [RbacPermissionController::class, 'listPermissions'])->name('list');
        Route::post('/', [RbacPermissionController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{permission}', [RbacPermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [RbacPermissionController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:Settings.access')->prefix('/settings/rbac/menu-match')->name('settings.rbac.menu-match.')->group(function () {
        Route::get('/', [RbacMenuMatchController::class, 'index'])->name('index');
        Route::get('/list', [RbacMenuMatchController::class, 'listMappings'])->name('list');
        Route::post('/save', [RbacMenuMatchController::class, 'updateMappings'])->name('save');
        Route::get('/available-menus', [RbacMenuMatchController::class, 'listAvailableMenus'])->name('available-menus');
        Route::post('/assign', [RbacMenuMatchController::class, 'assignMapping'])->name('assign');
    });

    Route::middleware('permission:menu:modules.project-management.item-issue')->group(function () {
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
    });

    // Laravel expects /home after login, so redirect it to dashboard
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    });

    // ===========================================
    // MASTER MODULES
    // ===========================================

    // Purchase Requisition Module
    Route::middleware('permission:menu:modules.procurement.purch-req')->group(function () {
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
    });

    // Goods Receive Note (GRN) Module
    Route::middleware('permission:menu:modules.procurement.grn')->group(function () {
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
    });

    // Other module stubs (quotation, PO, inventory, …)
    Route::get('/quotations', function () {
        return 'Quotation Module - Coming Soon';
    })->name('quotations.index');

    Route::get('/purchase-orders', function () {
        return 'Purchase Order Module - Coming Soon';
    })->name('purchase-orders.index');

    Route::get('/inventory', function () {
        return 'Inventory Module - Coming Soon';
    })->name('inventory.index');

    Route::get('/vendors', function () {
        return 'Vendors Module - Coming Soon';
    })->name('vendors.index');

    Route::get('/customers', function () {
        return 'Customers Module - Coming Soon';
    })->name('customers.index');

    Route::get('/reports', function () {
        return 'Reports Module - Coming Soon';
    })->name('reports.index');
});

// ===========================================
// DEBUG ROUTES (Remove in production)
// ===========================================
Route::get('/debug-config', function () {
    $config = [
        'app_url' => env('APP_URL'),
        'client_id_set' => ! empty(env('MICROSOFT_CLIENT_ID')),
        'client_secret_set' => ! empty(env('MICROSOFT_CLIENT_SECRET')),
        'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
        'tenant_id_valid' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', env('MICROSOFT_TENANT_ID')),
        'status' => 'READY',
    ];

    return response()->json($config);
});

Route::get('/debug-auth', function () {
    return [
        'authenticated' => auth()->check(),
        'user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ] : null,
        'intended_url' => session()->get('url.intended'),
        'session_data' => session()->all(),
    ];
});
