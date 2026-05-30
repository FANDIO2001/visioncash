<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/auth/2fa/enable', [AuthController::class, 'enable2FA'])->middleware('auth:sanctum');
    Route::post('/auth/2fa/verify', [AuthController::class, 'verify2FA'])->middleware('auth:sanctum');
    Route::post('/auth/2fa/disable', [AuthController::class, 'disable2FA'])->middleware('auth:sanctum');
    Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('/profile/select-currency', [ProfileController::class, 'selectCurrency'])->middleware('auth:sanctum');
    Route::post('/profile/select-language', [ProfileController::class, 'selectLanguage'])->middleware('auth:sanctum');
    Route::delete('/profile', [ProfileController::class, 'deleteAccount'])->middleware('auth:sanctum');
    Route::get('/profile/export', [ProfileController::class, 'exportData'])->middleware('auth:sanctum');
    Route::post('/accounts', [AccountController::class, 'create'])->middleware('auth:sanctum');
});