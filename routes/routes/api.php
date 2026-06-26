<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AiController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::apiResource('products', ProductController::class);

    Route::get('/sales',      [SaleController::class, 'index']);
    Route::post('/sales',     [SaleController::class, 'store']);
    Route::get('/sales/{id}', [SaleController::class, 'show']);

    Route::prefix('accounting')->group(function () {
        Route::get('/accounts',      [AccountController::class, 'index']);
        Route::get('/journal',       [AccountController::class, 'journal']);
        Route::get('/profit-loss',   [AccountController::class, 'profitLoss']);
        Route::get('/balance-sheet', [AccountController::class, 'balanceSheet']);
    });

    Route::prefix('ai')->group(function () {
        Route::get('/dashboard',        [AiController::class, 'dashboard']);
        Route::get('/forecast',         [AiController::class, 'forecast']);
        Route::get('/inventory-alerts', [AiController::class, 'inventoryAlerts']);
        Route::get('/profit-analysis',  [AiController::class, 'profitAnalysis']);
    });
});
