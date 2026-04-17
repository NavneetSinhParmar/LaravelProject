<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\ClientsController;
use App\Http\Controllers\Api\TestimonialsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
// Public GET endpoints restricted by allowed origin and method
Route::middleware(\App\Http\Middleware\RestrictApiByOrigin::class)->group(function (): void {
    Route::get('sliders', [SliderController::class, 'index']);
    Route::get('sliders/{id}', [SliderController::class, 'show']);

    Route::get('portfolio', [PortfolioController::class, 'index']);
    Route::get('portfolio/{id}', [PortfolioController::class, 'show']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    Route::get('clients', [ClientsController::class, 'index']);
    Route::get('clients/{id}', [ClientsController::class, 'show']);

    Route::get('testimonials', [TestimonialsController::class, 'index']);
    Route::get('testimonials/{id}', [TestimonialsController::class, 'show']);
});
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

    // Clients API routes for token-based auth
    Route::apiResource('clients', ClientsController::class);
    Route::post('clients/{client}', [ClientsController::class, 'update']);

    // testimonial API routes for token-based auth
    Route::apiResource('testimonials', TestimonialsController::class);
    Route::post('testimonials/{testimonial}', [TestimonialsController::class, 'update']);

});

Route::get('/cms', [CmsPageController::class, 'index']);
