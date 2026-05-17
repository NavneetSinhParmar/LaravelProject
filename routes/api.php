<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\PageSlugController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\ClientsController;
use App\Http\Controllers\Api\TestimonialsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Public read (website) OR admin read when Bearer token is sent.
Route::middleware('api.token.or.origin')->group(function (): void {
    Route::get('sliders', [SliderController::class, 'index']);
    Route::get('sliders/{id}', [SliderController::class, 'show']);

    Route::get('portfolio', [PortfolioController::class, 'index']);
    Route::get('portfolio/{portfolio}', [PortfolioController::class, 'show']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    Route::get('clients', [ClientsController::class, 'index']);
    Route::get('clients/{client}', [ClientsController::class, 'show']);

    Route::get('testimonials', [TestimonialsController::class, 'index']);
    Route::get('testimonials/{testimonial}', [TestimonialsController::class, 'show']);
});

Route::post('products/{id}/download', [ProductController::class, 'download'])->whereNumber('id');

Route::middleware('api.token')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/cms', [CmsPageController::class, 'store']);
    Route::put('/cms/{id}', [CmsPageController::class, 'update']);
    Route::delete('/cms/{id}', [CmsPageController::class, 'destroy']);

    Route::get('portfolio-categories', [PortfolioController::class, 'categories']);
    Route::apiResource('categories', CategoriesController::class);
    Route::post('categories/{category}', [CategoriesController::class, 'update']);

    Route::get('pageslug', [PageSlugController::class, 'index']);
    Route::post('pageslug', [PageSlugController::class, 'store']);
    Route::get('pageslug/{id}', [PageSlugController::class, 'show'])->whereNumber('id');
    Route::put('pageslug/{id}', [PageSlugController::class, 'update'])->whereNumber('id');
    Route::delete('pageslug/{id}', [PageSlugController::class, 'destroy'])->whereNumber('id');

    Route::apiResource('faqs', FaqController::class);
    Route::apiResource('portfolio', PortfolioController::class)->except(['index', 'show']);
    Route::post('portfolio/{portfolio}', [PortfolioController::class, 'update']);

    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update'])->whereNumber('id');
    Route::post('products/{id}', [ProductController::class, 'update'])->whereNumber('id');
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->whereNumber('id');

    Route::apiResource('sliders', SliderController::class)->except(['index', 'show']);
    Route::post('sliders/{id}', [SliderController::class, 'update']);

    Route::apiResource('clients', ClientsController::class)->except(['index', 'show']);
    Route::post('clients/{client}', [ClientsController::class, 'update']);

    Route::apiResource('testimonials', TestimonialsController::class)->except(['index', 'show']);
    Route::post('testimonials/{testimonial}', [TestimonialsController::class, 'update']);
});

Route::get('/cms', [CmsPageController::class, 'index']);
