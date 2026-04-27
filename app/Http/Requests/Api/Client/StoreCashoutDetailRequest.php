<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashoutDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:crypto,bank'],
            'is_default' => ['nullable', 'boolean'],
            // Crypto
            'crypto_wallet_address' => ['required_if:type,crypto', 'nullable', 'string'],
            'crypto_network' => ['nullable', 'string', 'max:100'],
            // Bank
            'account_holder' => ['required_if:type,bank', 'nullable', 'string', 'max:255'],
            'bank_name' => ['required_if:type,bank', 'nullable', 'string', 'max:255'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'routing_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:100'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
        ];
    }
}
