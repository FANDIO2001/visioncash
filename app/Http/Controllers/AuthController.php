<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No user found with this email address.'],
            ]);
        }

        // Generate a password reset token
        $token = \Illuminate\Support\Str::random(60);

        // Store the token in password_resets table
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send the token via email
        $user->notify(new PasswordResetNotification($token));

        return response()->json([
            'message' => 'Password reset link sent to your email.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the reset token
        $resetToken = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired reset token.'],
            ]);
        }

        // Check if token is expired (10 minutes)
        if (now()->diffInMinutes($resetToken->created_at) > 10) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'token' => ['Reset token has expired.'],
            ]);
        }

        // Reset the password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }
}
