<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SelectCurrencyRequest;
use App\Http\Requests\SelectLanguageRequest;
use App\Http\Requests\DeleteAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'default_currency' => $user->default_currency,
            'language' => $user->preferred_language ?? 'fr',
            'role' => $user->role ?? 'user',
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        if ($request->has('first_name')) {
            $user->first_name = $request->first_name;
        }

        if ($request->has('last_name')) {
            $user->last_name = $request->last_name;
        }

        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        if ($request->has('avatar_url')) {
            $user->avatar_url = $request->avatar_url;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar_url' => $user->avatar_url,
                'default_currency' => $user->default_currency,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    public function selectCurrency(SelectCurrencyRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->default_currency = $request->currency;
        $user->save();

        return response()->json([
            'message' => 'Currency selected successfully.',
            'currency' => $user->default_currency,
        ]);
    }

    public function selectLanguage(SelectLanguageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->preferred_language = $request->language;
        $user->save();

        return response()->json([
            'message' => 'Language selected successfully.',
            'language' => $user->preferred_language,
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.'],
            ]);
        }

        // Delete all tokens
        $user->tokens()->delete();

        // Delete user (soft delete)
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }

    public function exportData(): JsonResponse
    {
        $user = Auth::user();

        $data = [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar_url' => $user->avatar_url,
                'default_currency' => $user->default_currency,
                'preferred_language' => $user->preferred_language,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'deleted_at' => $user->deleted_at,
            ],
            'accounts' => $user->accounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_type_id' => $account->account_type_id,
                    'account_name' => $account->account_name,
                    'account_number' => $account->account_number,
                    'balance' => $account->balance,
                    'currency' => $account->currency,
                    'is_active' => $account->is_active,
                    'created_at' => $account->created_at,
                    'updated_at' => $account->updated_at,
                ];
            }),
            'two_factor_auth_settings' => $user->twoFactorAuthSettings->first() ? [
                'id' => $user->twoFactorAuthSettings->first()->id,
                'method' => $user->twoFactorAuthSettings->first()->method,
                'enabled' => $user->twoFactorAuthSettings->first()->enabled,
                'last_used_at' => $user->twoFactorAuthSettings->first()->last_used_at,
                'created_at' => $user->twoFactorAuthSettings->first()->created_at,
                'updated_at' => $user->twoFactorAuthSettings->first()->updated_at,
            ] : null,
            'export_date' => now()->toIso8601String(),
        ];

        return response()->json($data);
    }
}
