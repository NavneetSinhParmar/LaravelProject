<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\Api\ClientsController;
use App\Http\Controllers\Api\TestimonialsController;

// Route::get('/check-db', function () {
//     try {
//         DB::connection()->getPdo();
//         return 'DB Connected! Database: ' . DB::connection()->getDatabaseName();
//     } catch (\Exception $e) {
//         return 'DB Error: ' . $e->getMessage();
//     }
// });

// Route::get('/run-migrate', function () {
//     Artisan::call('migrate:fresh', ['--force' => true]);
//     return Artisan::output();
// });

// Route::get('/clear-config', function () {
//     Artisan::call('config:clear');
//     Artisan::call('cache:clear');
//     return 'Cleared!';
// });

// Route::get('/run-seed', function () {
//     Artisan::call('db:seed', ['--force' => true]);
//     return Artisan::output();  // shows result
// });


Route::get('/', function () {
    return redirect()->route('login');
});

/** Laravel's `auth` middleware and the framework default `redirectGuestsTo` expect a route named `login`. */
Route::get('/login', [FrontendController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [FrontendController::class, 'dashboard'])->name('frontend.dashboard');

    Route::get('/dashboard/sliders', [FrontendController::class, 'sliders'])->name('frontend.sliders');
    Route::get('/dashboard/clients', [FrontendController::class, 'clients'])->name('frontend.clients');
    Route::get('/dashboard/testimonials', [FrontendController::class, 'testimonials'])->name('frontend.testimonials');
    Route::get('/dashboard/products', [FrontendController::class, 'products'])->name('frontend.products');

    // Portfolio pages
    Route::get('/dashboard/portfolio', [FrontendController::class, 'portfolio'])->name('frontend.portfolio');
    Route::get('/dashboard/categories', [FrontendController::class, 'categories'])->name('frontend.categories');

    // Page Slugs admin UI
    Route::get('/dashboard/pageslug', function () {
        return view('frontend.pageslug');
    })->name('frontend.pageslug');

    // FAQs admin UI
    Route::get('/dashboard/faq', function () {
        return view('frontend.faqs');
    })->name('frontend.faq');

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

    // =========================
    // Clients CRUD (web)
    // =========================
    Route::get('/Clients', [ClientsController::class, 'index']);
    Route::get('/Clients/{id}', [ClientsController::class, 'show']);
    Route::post('/Clients', [ClientsController::class, 'store']);
    Route::match(['put', 'post'], '/Clients/{id}', [ClientsController::class, 'update']);
    Route::delete('/Clients/{id}', [ClientsController::class, 'destroy']);

    // =========================
    // Testimonials CRUD (web)
    // =========================
    Route::get('/testimonials', [TestimonialsController::class, 'index']);
    Route::get('/testimonials/{id}', [TestimonialsController::class, 'show']);
    Route::post('/testimonials', [TestimonialsController::class, 'store']);
    Route::match(['put', 'post'], '/testimonials/{id}', [TestimonialsController::class, 'update']);
    Route::delete('/testimonials/{id}', [TestimonialsController::class, 'destroy']);
});

// Web endpoint to accept API token and create a session
Route::post('/web/login', [WebAuthController::class, 'tokenLogin']);
Route::post('/web/logout', [WebAuthController::class, 'logout']);