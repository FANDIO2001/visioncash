<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use Illuminate\Http\JsonResponse;

class AccountTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = AccountType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'icon_url']);

        return response()->json([
            'data' => $types,
        ]);
    }
}
