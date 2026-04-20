<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.bearer')->group(function () {
    // Companies first (master), then projects (reference company_id), then other APIs.
    Route::apiResource('/companies', CompanyController::class);
    Route::apiResource('/projects', ProjectController::class);
    Route::post('/customers', [CustomerController::class, 'store']);
});
