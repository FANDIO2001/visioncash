<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_name' => 'sometimes|required|string|max:255',
            'account_type_id' => 'sometimes|required|exists:account_types,id',
            'currency' => 'sometimes|required|string|size:3',
            'color' => 'nullable|string|size:7',
            'iban' => 'nullable|string|max:34',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
