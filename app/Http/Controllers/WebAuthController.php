<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    public function tokenLogin(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::query()->where('api_token', hash('sha256', $data['token']))->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['message' => 'Logged in']);
    }
}
