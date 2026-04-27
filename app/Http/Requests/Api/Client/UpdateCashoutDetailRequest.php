<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCashoutDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'in:crypto,bank'],
            'is_default' => ['nullable', 'boolean'],
            'crypto_wallet_address' => ['sometimes', 'nullable', 'string'],
            'crypto_network' => ['nullable', 'string', 'max:100'],
            'account_holder' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'routing_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:100'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
        ];
    }
}
