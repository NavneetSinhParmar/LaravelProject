<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\WebAuthController;


Route::get('/', function () {
    return redirect()->route('login');
});

/** Laravel's `auth` middleware and the framework default `redirectGuestsTo` expect a route named `login`. */
Route::get('/login', [FrontendController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [FrontendController::class, 'dashboard'])->name('frontend.dashboard');

    Route::get('/dashboard/sliders', [FrontendController::class, 'sliders'])->name('frontend.sliders');
    Route::get('/dashboard/products', [FrontendController::class, 'products'])->name('frontend.products');

    // NEW: Portfolio Page
    Route::get('/dashboard/portfolio', [FrontendController::class, 'portfolio'])->name('frontend.portfolio');

    Route::resource('users', UserController::class);

    // Slider UI
    Route::get('/slider', function () {
        return view('admin.sliders');
    })->name('frontend.slider');

    Route::redirect('/portfolio', '/dashboard/portfolio');

    // =========================
    // Slider CRUD (web)
    // =========================
    Route::get('/sliders', [SliderController::class, 'index']);
    Route::get('/sliders/{id}', [SliderController::class, 'show']);
    Route::post('/sliders', [SliderController::class, 'store']);
    Route::match(['put', 'post'], '/sliders/{id}', [SliderController::class, 'update']);
    Route::delete('/sliders/{id}', [SliderController::class, 'destroy']);

});

// Web endpoint to accept API token and create a session
Route::post('/web/login', [WebAuthController::class, 'tokenLogin']);
Route::post('/web/logout', [WebAuthController::class, 'logout']);
