<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'earning_period_id' => $this->earning_period_id,
            'type' => $this->type,
            'btc_amount' => $this->btc_amount,
            'fiat_amount' => $this->fiat_amount,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'requested_at' => $this->requested_at,
            'processed_at' => $this->processed_at,
            'notes' => $this->notes,
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'earning_period' => EarningPeriodResource::make($this->whenLoaded('earningPeriod')),
            'cashout' => CashoutResource::make($this->whenLoaded('cashout')),
            'stored_earning' => StoredEarningResource::make($this->whenLoaded('storedEarning')),
            'created_at' => $this->created_at,
        ];
    }
}
