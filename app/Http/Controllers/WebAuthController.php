<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    public function tokenLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = User::query()->where('api_token', hash('sha256', $data['token']))->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid or expired token.'], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['message' => 'Session created.']);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Web session cleared.']);
    }
}
