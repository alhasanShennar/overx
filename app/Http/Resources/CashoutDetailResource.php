<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashoutDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'label' => $this->label,
            'type' => $this->type,
            'is_default' => $this->is_default,
            // Crypto
            'crypto_wallet_address' => $this->when($this->type === 'crypto', $this->crypto_wallet_address),
            'crypto_network' => $this->when($this->type === 'crypto', $this->crypto_network),
            // Bank
            'account_holder' => $this->when($this->type === 'bank', $this->account_holder),
            'bank_name' => $this->when($this->type === 'bank', $this->bank_name),
            'swift_code' => $this->when($this->type === 'bank', $this->swift_code),
            'routing_number' => $this->when($this->type === 'bank', $this->routing_number),
            'iban' => $this->when($this->type === 'bank', $this->iban),
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'created_at' => $this->created_at,
        ];
    }
}
