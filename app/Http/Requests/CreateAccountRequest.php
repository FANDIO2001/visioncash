<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateAccountRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'initial_balance' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'color' => 'nullable|string|size:7',
        ];
    }
}
