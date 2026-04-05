<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return new JsonResponse([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $plainToken = Str::random(60);
        $user->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return new JsonResponse([
            'message' => 'Login successful.',
            'token' => $plainToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->forceFill([
            'api_token' => null,
        ])->save();

        return new JsonResponse([
            'message' => 'Logout successful.',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return new JsonResponse([
            'user' => $request->user(),
        ]);
    }
}
