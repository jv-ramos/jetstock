<?php

use App\Http\Controllers\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1/products')->group(function () {
    Route::apiResource('/', ProductController::class)->only('index', 'store', 'update', 'destroy');
    Route::get('/{product}', [ProductController::class, 'show']);
});
