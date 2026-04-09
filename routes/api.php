<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SliderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('api.token')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/cms', [CmsPageController::class, 'store']);
    Route::put('/cms/{id}', [CmsPageController::class, 'update']);
    Route::delete('/cms/{id}', [CmsPageController::class, 'destroy']);

    Route::apiResource('portfolio', PortfolioController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('sliders', SliderController::class);
});

Route::get('/cms', [CmsPageController::class, 'index']);
Route::get('/sliders', [SliderController::class, 'index']);
