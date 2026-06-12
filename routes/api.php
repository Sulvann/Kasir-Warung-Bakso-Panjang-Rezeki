<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductAndRecipeController;
use App\Http\Controllers\Api\Cashier\TransactionController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Public/Auth Read Access
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('product-recipes', [ProductAndRecipeController::class, 'index']);
    Route::get('product-recipes/{product}', [ProductAndRecipeController::class, 'show']);

    Route::middleware([IsAdmin::class])->group(function () {
        // Admin Write Access
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('product-recipes', [ProductAndRecipeController::class, 'store']);
        Route::post('product-recipes/{product}', [ProductAndRecipeController::class, 'update']);
        Route::delete('product-recipes/{product}', [ProductAndRecipeController::class, 'destroy']);

        Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
    });

    Route::apiResource('transactions', TransactionController::class)->only(['index', 'store', 'show']);
});