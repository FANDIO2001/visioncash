<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\AccountBalanceHistoryRequest;
use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = Account::with('accountType')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $accounts,
        ]);
    }

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

        AccountBalanceHistory::create([
            'account_id' => $account->id,
            'balance' => $account->balance,
            'recorded_at' => now(),
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

    public function update(UpdateAccountRequest $request, int $id): JsonResponse
    {
        $account = Account::where('user_id', Auth::id())->findOrFail($id);

        $account->fill($request->validated());
        $account->save();
        $account->load('accountType');

        return response()->json([
            'message' => 'Account updated successfully.',
            'account' => $account,
        ]);
    }

    public function balanceHistory(AccountBalanceHistoryRequest $request, int $id): JsonResponse
    {
        $account = Account::where('user_id', Auth::id())->findOrFail($id);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : $endDate->copy()->subDays(30)->startOfDay();

        $stored = AccountBalanceHistory::query()
            ->where('account_id', $account->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();

        $source = 'history';
        $points = $stored;

        if ($stored->count() < 2) {
            $points = collect($this->buildBalanceTimeline($account, $startDate, $endDate));
            $source = 'computed';
        }

        return response()->json([
            'data' => $points->map(function ($point) use ($account) {
                if ($point instanceof AccountBalanceHistory) {
                    return [
                        'id' => $point->id,
                        'account_id' => $account->id,
                        'balance' => (float) $point->balance,
                        'recorded_at' => $point->recorded_at->toIso8601String(),
                    ];
                }

                return [
                    'id' => null,
                    'account_id' => $account->id,
                    'balance' => (float) $point['balance'],
                    'recorded_at' => Carbon::parse($point['recorded_at'])->toIso8601String(),
                ];
            })->values(),
            'account' => [
                'id' => $account->id,
                'account_name' => $account->account_name,
                'currency' => $account->currency,
                'current_balance' => (float) $account->balance,
                'color' => $account->color,
            ],
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'source' => $source,
        ]);
    }

    /**
     * @return array<int, array{balance: float, recorded_at: string}>
     */
    private function buildBalanceTimeline(Account $account, Carbon $startDate, Carbon $endDate): array
    {
        $transactionsBefore = Transaction::query()
            ->where('account_id', $account->id)
            ->where('transaction_date', '<', $startDate)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $balance = (float) $account->initial_balance;
        foreach ($transactionsBefore as $transaction) {
            $balance += $this->transactionDelta($transaction);
        }

        $points = [[
            'balance' => $balance,
            'recorded_at' => $startDate->toIso8601String(),
        ]];

        $transactionsInPeriod = Transaction::query()
            ->where('account_id', $account->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        foreach ($transactionsInPeriod as $transaction) {
            $balance += $this->transactionDelta($transaction);
            $points[] = [
                'balance' => $balance,
                'recorded_at' => Carbon::parse($transaction->transaction_date)->toIso8601String(),
            ];
        }

        $endPointDate = Carbon::parse(end($points)['recorded_at']);
        $closingDate = $endDate->lessThan(now()) ? $endDate : now();

        if ($transactionsInPeriod->isEmpty() || $closingDate->greaterThan($endPointDate)) {
            $points[] = [
                'balance' => (float) $account->balance,
                'recorded_at' => $closingDate->toIso8601String(),
            ];
        }

        return $points;
    }

    private function transactionDelta(Transaction $transaction): float
    {
        $amount = (float) $transaction->amount;

        return $transaction->transaction_type === 'income' ? $amount : -$amount;
    }

    private function generateAccountNumber(int $userId): string
    {
        do {
            $accountNumber = 'VC' . str_pad($userId, 4, '0', STR_PAD_LEFT) . Str::random(8);
        } while (Account::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}
