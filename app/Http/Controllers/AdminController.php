<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $usersThisMonth = User::where('created_at', '>=', $startOfMonth)->count();
        $usersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $accountsThisMonth = Account::where('created_at', '>=', $startOfMonth)->count();
        $transactionsThisMonth = Transaction::where('created_at', '>=', $startOfMonth)->count();

        $userGrowth = collect(range(5, 0))->map(function (int $monthsAgo) use ($now) {
            $month = $now->copy()->subMonths($monthsAgo);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('m/Y'),
                'count' => User::whereBetween('created_at', [$start, $end])->count(),
            ];
        })->values();

        $subscriptionsByStatus = Subscription::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'count' => (int) $row->count]);

        $subscriptionsByPlan = Subscription::query()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select('plans.name as plan_name', DB::raw('count(*) as count'))
            ->groupBy('plans.id', 'plans.name')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['plan_name' => $row->plan_name, 'count' => (int) $row->count]);

        $mrrXaf = Subscription::query()
            ->where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly_xaf');

        $recentUsers = User::orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'role', 'is_active', 'created_at']);

        $recentAccounts = Account::with(['user:id,first_name,last_name,email', 'accountType:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentTransactions = Transaction::with(['user:id,first_name,last_name', 'account:id,account_name'])
            ->orderByDesc('transaction_date')
            ->limit(8)
            ->get();

        return response()->json([
            'data' => [
                'kpis' => [
                    'users_count' => User::count(),
                    'active_users_count' => User::where('is_active', true)->count(),
                    'admins_count' => User::where('role', 'admin')->count(),
                    'verified_users_count' => User::whereNotNull('email_verified_at')->count(),
                    'accounts_count' => Account::count(),
                    'active_accounts_count' => Account::where('is_active', true)->count(),
                    'transactions_count' => Transaction::count(),
                    'budgets_count' => Budget::count(),
                    'categories_count' => Category::count(),
                    'plans_count' => Plan::count(),
                    'subscriptions_count' => Subscription::count(),
                    'active_subscriptions_count' => Subscription::where('status', 'active')->count(),
                    'trialing_subscriptions_count' => Subscription::where('status', 'trialing')->count(),
                ],
                'financial' => [
                    'total_balance' => (float) Account::sum('balance'),
                    'income_total' => (float) Transaction::where('transaction_type', 'income')->sum('amount'),
                    'expense_total' => (float) Transaction::where('transaction_type', 'expense')->sum('amount'),
                    'mrr_xaf' => (float) $mrrXaf,
                ],
                'growth' => [
                    'users_this_month' => $usersThisMonth,
                    'users_last_month' => $usersLastMonth,
                    'users_growth_percent' => $this->growthPercent($usersLastMonth, $usersThisMonth),
                    'accounts_this_month' => $accountsThisMonth,
                    'transactions_this_month' => $transactionsThisMonth,
                ],
                'charts' => [
                    'users_by_month' => $userGrowth,
                    'subscriptions_by_status' => $subscriptionsByStatus,
                    'subscriptions_by_plan' => $subscriptionsByPlan,
                ],
                'recent' => [
                    'users' => $recentUsers,
                    'accounts' => $recentAccounts,
                    'transactions' => $recentTransactions,
                ],
                'generated_at' => $now->toIso8601String(),
            ],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate($request->integer('per_page', 20));

        return response()->json($users);
    }

    public function accounts(Request $request): JsonResponse
    {
        $query = Account::with(['user:id,first_name,last_name,email', 'accountType'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate($request->integer('per_page', 20));

        return response()->json($accounts);
    }

    public function plans(): JsonResponse
    {
        $plans = Plan::orderBy('price_monthly_xaf')->get();

        return response()->json(['data' => $plans]);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with(['user:id,first_name,last_name,email', 'plan:id,name'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($subscriptions);
    }

    public function roles(): JsonResponse
    {
        return response()->json([
            'data' => [
                ['id' => 'user', 'label' => 'Utilisateur', 'description' => 'Accès standard au dashboard personnel'],
                ['id' => 'admin', 'label' => 'Administrateur', 'description' => 'Accès complet + panneau d\'administration'],
            ],
        ]);
    }

    public function updateUserRole(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id && $request->input('role') !== 'admin') {
            return response()->json(['message' => 'Vous ne pouvez pas retirer votre propre rôle admin.'], 422);
        }

        $user->role = $request->input('role');
        $user->save();

        return response()->json([
            'message' => 'Rôle mis à jour.',
            'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'role', 'is_active']),
        ]);
    }

    private function growthPercent(int $previous, int $current): ?float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
