<?php

use App\Http\Controllers\GrnController;
use App\Http\Controllers\ItemIssueController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\PurchReqController;
use Illuminate\Support\Facades\Route;

// ─── Project Management ───────────────────────────────────────────────────────

Route::middleware(['auth', 'permission:menu:modules.project-management.item-issue'])->group(function () {
    Route::get('/modules/project-management/item-issue', [ItemIssueController::class, 'index'])
        ->name('modules.project-management.item-issue');

    Route::prefix('/modules/project-management/item-issue/api')->group(function () {
        Route::post('/items/lookup',    [ItemIssueController::class, 'lookupItems'])->name('modules.project-management.item-issue.api.items.lookup');
        Route::post('/projects/lookup', [ItemIssueController::class, 'lookupProjects'])->name('modules.project-management.item-issue.api.projects.lookup');
        Route::post('/post',            [ItemIssueController::class, 'post'])->name('modules.project-management.item-issue.api.post');
        Route::post('/onhand',          [ItemIssueController::class, 'lookupOnHand'])->name('modules.project-management.item-issue.api.onhand');
        Route::post('/units',           [ItemIssueController::class, 'lookupUnits'])->name('modules.project-management.item-issue.api.units');
        Route::get('/journals/{journal}',    [ItemIssueController::class, 'showJournal'])->name('modules.project-management.item-issue.api.journals.show');
        Route::delete('/journals/{journal}', [ItemIssueController::class, 'destroyJournal'])->name('modules.project-management.item-issue.api.journals.destroy');
    });
});

// ─── Procurement – Purchase Requisition ──────────────────────────────────────

Route::middleware(['auth', 'permission:menu:modules.procurement.purch-req'])->group(function () {
    Route::get('/purchase-requisitions', [PurchaseRequisitionController::class, 'index'])->name('purchase-requisitions.index');
    Route::post('/purchase-requisitions/api/post', [PurchaseRequisitionController::class, 'post'])->name('purchase-requisitions.api.post');

    Route::get('/modules/procurement/purch-req', [PurchReqController::class, 'index'])->name('modules.procurement.purch-req');

    Route::prefix('/modules/procurement/purch-req/api')->group(function () {
        Route::post('/post',                        [PurchReqController::class, 'post'])->name('modules.procurement.purch-req.post');
        Route::post('/save',                        [PurchReqController::class, 'saveDraft'])->name('modules.procurement.purch-req.save');
        Route::put('/drafts/{journal}',             [PurchReqController::class, 'updateDraft'])->name('modules.procurement.purch-req.drafts.update');
        Route::get('/journals/{journal}',           [PurchReqController::class, 'showJournal'])->name('modules.procurement.purch-req.journals.show');
        Route::delete('/journals/{journal}',        [PurchReqController::class, 'destroyJournal'])->name('modules.procurement.purch-req.journals.destroy');
        Route::post('/units',                       [PurchReqController::class, 'lookupUnits'])->name('modules.procurement.purch-req.api.units');
        Route::post('/catalog',                     [PurchReqController::class, 'lookupCatalog'])->name('modules.procurement.purch-req.api.catalog');
        Route::get('/department-managers',          [PurchReqController::class, 'lookupDepartmentManagers'])->name('modules.procurement.purch-req.api.department-managers');
    });

    Route::get('/modules/procurement/purch-req/{journal}/attachments/{index}',
        [PurchReqController::class, 'downloadAttachment'])
        ->name('modules.procurement.purch-req.attachment')
        ->where('index', '[0-9]+');

    Route::get('/modules/procurement/purch-req/{journal}/attachments/{index}/base64',
        [PurchReqController::class, 'viewBase64'])
        ->name('modules.procurement.purch-req.attachment.base64')
        ->where('index', '[0-9]+');
});

// ─── Procurement – GRN ───────────────────────────────────────────────────────

Route::middleware(['auth', 'permission:menu:modules.procurement.grn'])->group(function () {
    Route::get('/modules/procurement/grn',       [GrnController::class, 'index'])->name('modules.procurement.grn');
    Route::get('/modules/procurement/grn/view',  [GrnController::class, 'view'])->name('modules.procurement.grn.view');

    Route::prefix('/modules/procurement/grn/api')->group(function () {
        Route::post('/search', [GrnController::class, 'search'])->name('modules.procurement.grn.api.search');
        Route::post('/lines',  [GrnController::class, 'lineDetails'])->name('modules.procurement.grn.api.lines');
        Route::post('/post',   [GrnController::class, 'postPackingSlip'])->name('modules.procurement.grn.api.post');
    });
});
