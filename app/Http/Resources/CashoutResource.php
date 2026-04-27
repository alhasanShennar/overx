<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'btc_amount' => $this->btc_amount,
            'receipt_url' => $this->receipt ? asset('storage/' . $this->receipt) : null,
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'cashout_detail' => CashoutDetailResource::make($this->whenLoaded('cashoutDetail')),
            'created_at' => $this->created_at,
        ];
    }
}
