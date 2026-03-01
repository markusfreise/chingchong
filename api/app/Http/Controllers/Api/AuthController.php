<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'data' => new UserResource(Auth::user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function createToken(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken(
            $request->device_name,
            ['time-entry:create', 'time-entry:read', 'project:read', 'task:read']
        );

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'name' => $token->accessToken->name,
            ],
        ]);
    }
}
