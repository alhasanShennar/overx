<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradingCashoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trading_period_id' => $this->trading_period_id,
            'amount' => (float) $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'receipt_url' => $this->receipt ? asset('storage/' . $this->receipt) : null,
            'notes' => $this->notes,
            'cashout_detail' => $this->whenLoaded('cashoutDetail', fn () => [
                'id' => $this->cashoutDetail->id,
                'label' => $this->cashoutDetail->label ?? $this->cashoutDetail->type,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
