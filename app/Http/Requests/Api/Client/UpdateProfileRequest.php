<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'    => ['sometimes', 'nullable', 'string', 'max:50'],
            'passport' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
