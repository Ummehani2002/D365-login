<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ItemCategorySyncController;
use App\Http\Controllers\Api\ItemSyncController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\ItemIssueController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.bearer')->group(function () {
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::apiResource('/companies', CompanyController::class);
    Route::apiResource('/projects', ProjectController::class);
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
