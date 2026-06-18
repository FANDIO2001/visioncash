<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\Enable2FARequest;
use App\Http\Requests\Verify2FARequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\TwoFactorAuthSettings;
use App\Notifications\EmailVerificationNotification;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //register 
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        // Generate email verification code (6 digits)
        $code = $this->generateSixDigitCode();

        // Store the verification code
        \DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Send verification email
        $user->notify(new EmailVerificationNotification($code));

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user' => new UserResource($user),
        ], 201);
    }

    //login
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

    //logout
    public function logout(): JsonResponse
    {
        $user = Auth::user();

        // Delete all tokens for the user (access and refresh tokens)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    //forgot password
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No user found with this email address.'],
            ]);
        }

        // Generate a 6-digit password reset code
        $code = $this->generateSixDigitCode();

        // Store the code in password_reset_tokens table
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Send the code via email
        $user->notify(new PasswordResetNotification($code));

        return response()->json([
            'message' => 'Un code de réinitialisation à 6 chiffres a été envoyé à votre adresse email.',
        ]);
    }

    //reset password
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'token' => 'required|string|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the reset code
        $resetToken = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
            throw ValidationException::withMessages([
                'token' => ['Code de réinitialisation invalide ou expiré.'],
            ]);
        }

        // Check if code is expired (10 minutes)
        if (now()->diffInMinutes($resetToken->created_at) > 10) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'token' => ['Le code de réinitialisation a expiré.'],
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

    //verify email
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'token' => 'required|string|digits:6',
        ]);

        // Find the verification code
        $verificationToken = \DB::table('email_verification_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$verificationToken || !Hash::check($request->token, $verificationToken->token)) {
            throw ValidationException::withMessages([
                'token' => ['Code de vérification invalide ou expiré.'],
            ]);
        }

        // Check if code is expired (60 minutes)
        if (now()->diffInMinutes($verificationToken->created_at) > 60) {
            \DB::table('email_verification_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'token' => ['Le code de vérification a expiré.'],
            ]);
        }

        // Verify the user's email
        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->save();

        // Delete the verification token
        \DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Aucun compte trouvé avec cette adresse email.'],
            ]);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Cette adresse email est déjà vérifiée.',
            ]);
        }

        $code = $this->generateSixDigitCode();

        \DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        $user->notify(new EmailVerificationNotification($code));

        return response()->json([
            'message' => 'Un nouveau code de vérification a été envoyé à votre adresse email.',
        ]);
    }

    //refresh token
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        // Find the token in the database
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->refresh_token);

        if (!$token) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Invalid refresh token.'],
            ]);
        }

        // Check if the token is a refresh token
        if ($token->name !== 'refresh-token') {
            throw ValidationException::withMessages([
                'refresh_token' => ['This is not a refresh token.'],
            ]);
        }

        // Check if the token is expired
        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();
            throw ValidationException::withMessages([
                'refresh_token' => ['Refresh token has expired.'],
            ]);
        }

        $user = $token->tokenable;

        // Delete the old refresh token
        $token->delete();

        // Create new access token (15 minutes)
        $accessToken = $user->createToken('access-token', ['*'], now()->addMinutes(15));

        // Create new refresh token (30 days)
        $refreshToken = $user->createToken('refresh-token', ['*'], now()->addDays(30));

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'access_token_expires_at' => now()->addMinutes(15)->toIso8601String(),
            'refresh_token' => $refreshToken->plainTextToken,
            'refresh_token_expires_at' => now()->addDays(30)->toIso8601String(),
        ]);
    }
// enable 2FA
    public function enable2FA(Enable2FARequest $request): JsonResponse
    {
        $user = Auth::user();

        // Delete existing 2FA settings if any
        TwoFactorAuthSettings::where('user_id', $user->id)->delete();

        $twoFactorSettings = new TwoFactorAuthSettings();
        $twoFactorSettings->user_id = $user->id;
        $twoFactorSettings->method = $request->method;
        $twoFactorSettings->enabled = false; // Not enabled until verified

        if ($request->method === 'sms') {
            $twoFactorSettings->phone_number = $request->phone_number;
        } elseif ($request->method === 'totp') {
            // Generate TOTP secret
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $twoFactorSettings->totp_secret = $google2fa->generateSecretKey();
        }

        $twoFactorSettings->save();

        $response = [
            'message' => '2FA setup initiated. Please verify to enable.',
            'method' => $twoFactorSettings->method,
        ];

        if ($request->method === 'totp') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $google2faQrCode = new \PragmaRX\Google2FAQRCode\Google2FA();
            $response['totp_secret'] = $twoFactorSettings->totp_secret;
            
            $otpAuthUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $twoFactorSettings->totp_secret
            );
            
            $response['qr_code_url'] = $google2faQrCode->getQRCodeInline(
                config('app.name'),
                $user->email,
                $twoFactorSettings->totp_secret
            );
        }

        return response()->json($response);
    }

    // verify 2FA
    public function verify2FA(Verify2FARequest $request): JsonResponse
    {
        $user = Auth::user();
        $twoFactorSettings = TwoFactorAuthSettings::where('user_id', $user->id)->first();

        if (!$twoFactorSettings) {
            throw ValidationException::withMessages([
                'code' => ['2FA not enabled for this account.'],
            ]);
        }

        if ($twoFactorSettings->method === 'totp') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valid = $google2fa->verifyKey($twoFactorSettings->totp_secret, $request->code);

            if (!$valid) {
                throw ValidationException::withMessages([
                    'code' => ['Invalid verification code.'],
                ]);
            }
        } elseif ($twoFactorSettings->method === 'sms') {
            // For SMS, we would verify against a stored OTP code
            // For now, we'll accept any 6-digit code for testing
            // In production, this should verify against a stored OTP code sent via SMS
            if (!preg_match('/^\d{6}$/', $request->code)) {
                throw ValidationException::withMessages([
                    'code' => ['Invalid verification code.'],
                ]);
            }
        }

        // Enable 2FA
        $twoFactorSettings->enabled = true;
        $twoFactorSettings->last_used_at = now();
        $twoFactorSettings->save();

        return response()->json([
            'message' => '2FA enabled successfully.',
        ]);
    }

    // disable 2FA
    public function disable2FA(): JsonResponse
    {
        $user = Auth::user();
        TwoFactorAuthSettings::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => '2FA disabled successfully.',
        ]);
    }

    private function generateSixDigitCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
