<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        // Pour l'API, nous allons créer un endpoint de vérification personnalisé
        // $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Delete existing tokens for this user
        $user->tokens()->delete();

        // Create access token (15 minutes)
        $accessToken = $user->createToken('access-token', ['*'], now()->addMinutes(15));

        // Create refresh token (30 days)
        $refreshToken = $user->createToken('refresh-token', ['*'], now()->addDays(30));

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'access_token_expires_at' => now()->addMinutes(15)->toIso8601String(),
            'refresh_token' => $refreshToken->plainTextToken,
            'refresh_token_expires_at' => now()->addDays(30)->toIso8601String(),
            'user' => new UserResource($user),
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = Auth::user();

        // Delete all tokens for the user (access and refresh tokens)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
