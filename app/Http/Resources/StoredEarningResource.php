<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoredEarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'transaction_id' => $this->transaction_id,
            'earning_period_id' => $this->earning_period_id,
            'btc_amount' => $this->btc_amount,
            'revenue_amount' => $this->revenue_amount,
            'stored_at' => $this->stored_at,
            'notes' => $this->notes,
            'earning_period' => EarningPeriodResource::make($this->whenLoaded('earningPeriod')),
            'created_at' => $this->created_at,
        ];
    }
}
