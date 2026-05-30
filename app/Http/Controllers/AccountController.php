<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function create(CreateAccountRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Generate unique account number
        $accountNumber = $this->generateAccountNumber($user->id);

        $account = Account::create([
            'user_id' => $user->id,
            'account_type_id' => $request->account_type_id,
            'account_number' => $accountNumber,
            'account_name' => $request->account_name,
            'is_active' => true,
            'currency' => $request->currency,
            'color' => $request->color,
            'initial_balance' => $request->initial_balance,
            'balance' => $request->initial_balance,
        ]);

        return response()->json([
            'message' => 'Account created successfully.',
            'account' => [
                'id' => $account->id,
                'account_name' => $account->account_name,
                'account_number' => $account->account_number,
                'account_type_id' => $account->account_type_id,
                'balance' => $account->balance,
                'currency' => $account->currency,
                'color' => $account->color,
                'is_active' => $account->is_active,
                'created_at' => $account->created_at,
            ],
        ], 201);
    }

    private function generateAccountNumber(int $userId): string
    {
        do {
            $accountNumber = 'VC' . str_pad($userId, 4, '0', STR_PAD_LEFT) . Str::random(8);
        } while (Account::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}
