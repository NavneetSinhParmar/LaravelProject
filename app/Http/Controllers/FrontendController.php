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
}
