<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ItemCategorySyncController;
use App\Http\Controllers\Api\ItemSalesTaxGroupController;
use App\Http\Controllers\Api\ItemUnitController;
use App\Http\Controllers\Api\ItemSyncController;
use App\Http\Controllers\Api\PoolController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SalesTaxGroupController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\BudgetResourceCodeController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\ItemIssueController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.bearer')->group(function () {
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::apiResource('/companies', CompanyController::class);
    Route::apiResource('/projects', ProjectController::class);

    /*
     * Back-compat aliases: some clients call `/api/pool` (singular).
     * Canonical routes remain `/api/pools` (apiResource).
     */
    Route::match(['get', 'head'], '/pool', [PoolController::class, 'index']);
    Route::post('/pool', [PoolController::class, 'store']);
    Route::delete('/pool', [PoolController::class, 'destroyAlias'])
        ->name('api.pool.destroy.alias');
    Route::post('/pool/sync-d365', [PoolController::class, 'syncFromD365'])
        ->name('api.pool.sync.alias');
    Route::match(['get', 'head'], '/pool/{pool}', [PoolController::class, 'show']);
    Route::put('/pool/{pool}', [PoolController::class, 'update']);
    Route::patch('/pool/{pool}', [PoolController::class, 'update']);
    Route::delete('/pool/{pool}', [PoolController::class, 'destroy']);

    Route::apiResource('/pools', PoolController::class);
    Route::post('/pools/sync-d365', [PoolController::class, 'syncFromD365'])
        ->name('api.pools.sync');
    Route::apiResource('/warehouses', WarehouseController::class);
    Route::apiResource('/budget-resource-codes', BudgetResourceCodeController::class);
    Route::get('/sites', [SiteController::class, 'index']);
    Route::post('/sites', [SiteController::class, 'store']);
    Route::delete('/sites/{site}', [SiteController::class, 'destroy']);
    /*
     * Singular alias (same resource as /api/sites); mirrors /masters/site naming.
     */
    Route::match(['get', 'head'], '/site', [SiteController::class, 'index']);
    Route::post('/site', [SiteController::class, 'store']);
    Route::delete('/site/{site}', [SiteController::class, 'destroy']);
    Route::get('/sales-tax-groups', [SalesTaxGroupController::class, 'index'])
        ->name('api.sales-tax-groups.index');
    Route::post('/sales-tax-groups', [SalesTaxGroupController::class, 'store'])
        ->name('api.sales-tax-groups.store');
    Route::delete('/sales-tax-groups/{sales_tax_group}', [SalesTaxGroupController::class, 'destroy'])
        ->name('api.sales-tax-groups.destroy');

    Route::get('/item-sales-tax-groups', [ItemSalesTaxGroupController::class, 'index'])
        ->name('api.item-sales-tax-groups.index');
    Route::post('/item-sales-tax-groups', [ItemSalesTaxGroupController::class, 'store'])
        ->name('api.item-sales-tax-groups.store');
    Route::delete('/item-sales-tax-groups/{item_sales_tax_group}', [ItemSalesTaxGroupController::class, 'destroy'])
        ->name('api.item-sales-tax-groups.destroy');

    Route::get('/item-units', [ItemUnitController::class, 'index'])->name('api.item-units.index');
    Route::post('/item-units', [ItemUnitController::class, 'store'])->name('api.item-units.store');
    Route::delete('/item-units/{item_unit}', [ItemUnitController::class, 'destroy'])->name('api.item-units.destroy');

    Route::get('/item-categories', [ItemCategorySyncController::class, 'index']);
    Route::post('/item-categories', [ItemCategorySyncController::class, 'store']);
    Route::get('/items', [ItemSyncController::class, 'index']);
    Route::post('/items', [ItemSyncController::class, 'store']);
    Route::post('/item-issue/items/lookup', [ItemIssueController::class, 'lookupItems'])
        ->name('api.item-issue.items.lookup');
    Route::post('/item-issue/projects/lookup', [ItemIssueController::class, 'lookupProjects'])
        ->name('api.item-issue.projects.lookup');
    Route::post('/item-issue/post', [ItemIssueController::class, 'post'])
        ->name('api.item-issue.post');
});
