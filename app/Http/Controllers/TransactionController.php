<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Models\TransactionAttachment;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['account', 'category'])
            ->where('user_id', Auth::id())
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        // Filtre par période
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->input('end_date'));
        }

        // Filtre par type (dépense/revenu)
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->input('transaction_type'));
        }

        // Filtre par compte
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filtre par montant (min/max)
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->input('min_amount'));
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->input('max_amount'));
        }

        if ($request->filled('limit')) {
            $query->limit((int) $request->input('limit'));
        }

        if ($request->filled('offset')) {
            $query->offset((int) $request->input('offset'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $account = Account::where('id', $validated['account_id'])
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Create the transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'account_id' => $validated['account_id'],
                'category_id' => $validated['category_id'],
                'amount' => $validated['amount'],
                'transaction_type' => $validated['transaction_type'],
                'description' => $validated['description'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'currency' => $account->currency,
                'is_manual' => true,
            ]);

            // Update the account balance
            if ($validated['transaction_type'] === 'expense') {
                $account->balance -= $validated['amount'];
            } elseif ($validated['transaction_type'] === 'income') {
                $account->balance += $validated['amount'];
            }
            $account->save();

            AccountBalanceHistory::create([
                'account_id' => $account->id,
                'balance' => $account->balance,
                'recorded_at' => now(),
            ]);

            // Handle attachment if any
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attachments/transactions', 'public');
                
                TransactionAttachment::create([
                    'transaction_id' => $transaction->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);

                $transaction->attachment_url = Storage::url($path);
                $transaction->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction added successfully.',
                'data' => $transaction->load(['account', 'category', 'attachments']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving the transaction.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
