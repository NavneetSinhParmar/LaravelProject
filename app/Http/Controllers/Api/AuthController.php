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

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plainToken = Str::random(60);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_token' => hash('sha256', $plainToken),
        ]);

        return new JsonResponse([
            'message' => 'Registration successful.',
            'token' => $plainToken,
            'user' => $user,
        ], 201);
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
