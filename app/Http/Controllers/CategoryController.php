<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhere('is_default', true);
            })
            ->where('is_active', true);

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where(function ($q) use ($type) {
                $q->where('category_type', $type)
                    ->orWhere('category_type', 'both');
            });
        }

        $categories = $query->orderBy('name')->get();

        return response()->json(['data' => $categories]);
    }
}
