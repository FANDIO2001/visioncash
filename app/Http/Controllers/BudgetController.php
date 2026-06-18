<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(): JsonResponse
    {
        $budgets = Budget::with('category')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $budgets,
        ]);
    }
}
