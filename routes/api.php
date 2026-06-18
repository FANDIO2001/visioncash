<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\AdminController;
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
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);
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
    Route::get('/accounts', [AccountController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/accounts', [AccountController::class, 'create'])->middleware('auth:sanctum');
    Route::put('/accounts/{id}', [AccountController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/accounts/{id}/balance-history', [AccountController::class, 'balanceHistory'])->middleware('auth:sanctum');
    Route::get('/account-types', [AccountTypeController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/transactions', [TransactionController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/transactions', [TransactionController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/budgets', [BudgetController::class, 'index'])->middleware('auth:sanctum');

    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/accounts', [AdminController::class, 'accounts']);
        Route::get('/plans', [AdminController::class, 'plans']);
        Route::get('/subscriptions', [AdminController::class, 'subscriptions']);
        Route::get('/roles', [AdminController::class, 'roles']);
        Route::patch('/users/{id}/role', [AdminController::class, 'updateUserRole']);
    });
});