<?php

use App\Http\Controllers\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1/products')->group(function () {
    Route::apiResource('/', ProductController::class)->only('index', 'store');
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::put('/{product}', [ProductController::class, 'update']);
    Route::delete('/{product}', [ProductController::class, 'destroy']);
    Route::get('/inventory/total', [ProductController::class, 'calculateTotalInventoryValue']);
    Route::post('/stockUpdate/{product}', [ProductController::class, 'stockUpdate']);
});
