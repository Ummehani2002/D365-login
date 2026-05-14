<?php

use App\Http\Controllers\Api\CurrencyController as ApiCurrencyController;
use App\Http\Controllers\Api\DepartmentManagerController as ApiDepartmentManagerController;
use App\Http\Controllers\Api\ItemSalesTaxGroupController as ApiItemSalesTaxGroupController;
use App\Http\Controllers\Api\ItemUnitController as ApiItemUnitController;
use App\Http\Controllers\Api\PoolController as ApiPoolController;
use App\Http\Controllers\Api\SalesTaxGroupController as ApiSalesTaxGroupController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\BudgetResourceCodeMasterController;
use App\Http\Controllers\CompanyMasterController;
use App\Http\Controllers\CurrencyMasterController;
use App\Http\Controllers\DepartmentManagerMasterController;
use App\Http\Controllers\FdLocationMasterController;
use App\Http\Controllers\WarrantyMasterController;
use App\Http\Controllers\ItemCategoryMasterController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemSalesTaxGroupMasterController;
use App\Http\Controllers\ItemUnitMasterController;
use App\Http\Controllers\PoolMasterController;
use App\Http\Controllers\ProjectMasterController;
use App\Http\Controllers\SalesTaxGroupMasterController;
use App\Http\Controllers\SiteMasterController;
use App\Http\Controllers\SizeMasterController;
use App\Http\Controllers\WarehouseMasterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'super_admin'])->group(function () {

    Route::get('/masters/company', [CompanyMasterController::class, 'index'])->name('masters.company.index');
    Route::post('/masters/company', [CompanyMasterController::class, 'store'])->name('masters.company.store');
    Route::post('/masters/company/quick-add', [CompanyMasterController::class, 'quickStore'])->name('masters.company.quick-add');
    Route::post('/masters/company/sync-d365', [CompanyMasterController::class, 'syncFromD365'])->name('masters.company.sync');

    Route::get('/masters/project', [ProjectMasterController::class, 'index'])->name('masters.project.index');
    Route::post('/masters/project/quick-add', [ProjectMasterController::class, 'quickStore'])->name('masters.project.quick-add');
    Route::post('/masters/project/sync-d365', [ProjectMasterController::class, 'syncFromD365'])->name('masters.project.sync');

    Route::get('/masters/pools', [PoolMasterController::class, 'index'])->name('masters.pools.index');
    Route::get('/masters/currencies', [CurrencyMasterController::class, 'index'])->name('masters.currencies.index');
    Route::get('/masters/site', [SiteMasterController::class, 'index'])->name('masters.site.index');
    Route::get('/masters/sizes', [SizeMasterController::class, 'index'])->name('masters.sizes.index');

    Route::get('/masters/categories', [ItemCategoryMasterController::class, 'index'])->name('masters.categories.index');
    Route::post('/masters/categories', [ItemCategoryMasterController::class, 'store'])->name('masters.categories.store');

    Route::get('/masters/items', [ItemMasterController::class, 'index'])->name('masters.items.index');
    Route::post('/masters/items', [ItemMasterController::class, 'store'])->name('masters.items.store');

    Route::get('/masters/item-sales-tax-groups', [ItemSalesTaxGroupMasterController::class, 'index'])->name('masters.item-sales-tax-groups.index');
    Route::get('/masters/sales-tax-groups', [SalesTaxGroupMasterController::class, 'index'])->name('masters.sales-tax-groups.index');
    Route::get('/masters/units', [ItemUnitMasterController::class, 'index'])->name('masters.units.index');

    Route::get('/masters/warehouses', [WarehouseMasterController::class, 'index'])->name('masters.warehouses.index');
    Route::post('/masters/warehouses', [WarehouseMasterController::class, 'store'])->name('masters.warehouses.store');
    Route::delete('/masters/warehouses/{warehouse}', [WarehouseMasterController::class, 'destroy'])->name('masters.warehouses.destroy');

    Route::get('/masters/fd-locations', [FdLocationMasterController::class, 'index'])->name('masters.fd-locations.index');
    Route::post('/masters/fd-locations', [FdLocationMasterController::class, 'store'])->name('masters.fd-locations.store');
    Route::delete('/masters/fd-locations/{fdLocation}', [FdLocationMasterController::class, 'destroy'])->name('masters.fd-locations.destroy');

    Route::get('/masters/warranty', [WarrantyMasterController::class, 'index'])->name('masters.warranty.index');
    Route::post('/masters/warranty', [WarrantyMasterController::class, 'store'])->name('masters.warranty.store');
    Route::delete('/masters/warranty/{warranty}', [WarrantyMasterController::class, 'destroy'])->name('masters.warranty.destroy');
    Route::get('/masters/budget-resource-codes', [BudgetResourceCodeMasterController::class, 'index'])->name('masters.budget-resource-codes.index');
    Route::post('/masters/budget-resource-codes', [BudgetResourceCodeMasterController::class, 'store'])->name('masters.budget-resource-codes.store');
    Route::delete('/masters/budget-resource-codes/{budgetResourceCode}', [BudgetResourceCodeMasterController::class, 'destroy'])->name('masters.budget-resource-codes.destroy');
    Route::get('/masters/department-managers', [DepartmentManagerMasterController::class, 'index'])->name('masters.department-managers.index');

    foreach (['colors' => 'Colors', 'styles' => 'Styles', 'locations' => 'Locations', 'batches' => 'Batches'] as $slug => $title) {
        Route::get("/masters/{$slug}", fn () => view('masters.placeholder', ['title' => $title]))->name("masters.{$slug}.index");
    }

    // Masters AJAX / API sub-routes
    Route::prefix('/masters/api')->name('masters.api.')->group(function () {
        Route::get('/sizes', [SizeController::class, 'index'])->name('sizes.index');
        Route::post('/sizes', [SizeController::class, 'store'])->name('sizes.store');
        Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('sizes.destroy');

        Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
        Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
        Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
        Route::get('/site', [SiteController::class, 'index'])->name('site.index');
        Route::post('/site', [SiteController::class, 'store'])->name('site.store');
        Route::delete('/site/{site}', [SiteController::class, 'destroy'])->name('site.destroy');

        Route::get('/pools', [ApiPoolController::class, 'index'])->name('pools.index');
        Route::post('/pools', [ApiPoolController::class, 'store'])->name('pools.store');
        Route::post('/pools/sync-d365', [ApiPoolController::class, 'syncFromD365'])->name('pools.sync-d365');
        Route::put('/pools/{pool}', [ApiPoolController::class, 'update'])->name('pools.update');
        Route::patch('/pools/{pool}', [ApiPoolController::class, 'update'])->name('pools.patch');
        Route::delete('/pools/{pool}', [ApiPoolController::class, 'destroy'])->name('pools.destroy');

        Route::get('/department-managers', [ApiDepartmentManagerController::class, 'index'])->name('department-managers.index');
        Route::post('/department-managers', [ApiDepartmentManagerController::class, 'store'])->name('department-managers.store');
        Route::delete('/department-managers/{department_manager}', [ApiDepartmentManagerController::class, 'destroy'])->name('department-managers.destroy');

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
