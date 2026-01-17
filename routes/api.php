<?php

use Illuminate\Support\Facades\Route;
Route::post('/customers', [CustomerController::class, 'store']);
