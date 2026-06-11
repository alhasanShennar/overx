<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;

class RequestTradingCashoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cashout_details_id' => ['nullable', 'integer', 'exists:cashout_details,id'],
        ];
    }
}
