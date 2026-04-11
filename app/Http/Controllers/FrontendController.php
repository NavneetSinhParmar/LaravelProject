<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    public function login(Request $request): View|RedirectResponse
    {
        if ($request->boolean('reauth')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (Auth::check()) {
            return redirect()->route('frontend.dashboard');
        }

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
