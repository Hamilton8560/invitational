<?php

use Illuminate\Support\Facades\Route;


Route::get('sales/statements', [App\Http\Controllers\SaleController::class, 'statements']);
Route::apiResource('sales', App\Http\Controllers\SaleController::class);
