<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class FrontendController extends Controller
{
    public function login(): View
    {
        return view('frontend.login');
    }

    public function dashboard(): View
    {
        return view('frontend.dashboard');
    }

    public function sliders(): View
    {
        return view('frontend.sliders');
    }

    public function products(): View
    {
        return view('frontend.products');
    }
}
