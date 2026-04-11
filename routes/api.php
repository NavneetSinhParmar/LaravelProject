<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SliderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('api.token')->group(function (): void {
    // Auth API
    Route::post('/logout', [AuthController::class, 'logout']);

    // User profile API
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/cms', [CmsPageController::class, 'store']);
    Route::put('/cms/{id}', [CmsPageController::class, 'update']);
    Route::delete('/cms/{id}', [CmsPageController::class, 'destroy']);

    // Portfolio API (categories route must be before {id} resource)
    Route::get('portfolio-categories', [PortfolioController::class, 'categories']);
    Route::apiResource('portfolio', PortfolioController::class);
    Route::post('portfolio/{portfolio}', [PortfolioController::class, 'update']);

    // product API routes for token-based auth (used by mobile app)
    Route::apiResource('products', ProductController::class);

    // slider API routes for token-based auth (used by mobile app)
    Route::apiResource('sliders', SliderController::class);
    Route::post('sliders/{id}', [SliderController::class, 'update']);
});

Route::get('/cms', [CmsPageController::class, 'index']);
