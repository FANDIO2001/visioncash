<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('accounts', 'id')->where('user_id', \Illuminate\Support\Facades\Auth::id()),
            ],
            'category_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', \Illuminate\Support\Facades\Auth::id())
                          ->orWhere('is_default', true);
                }),
            ],
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|string|in:expense,income',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120', // Max 5MB
        ];
    }
}
