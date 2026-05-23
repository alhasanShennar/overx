<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'period' => $this->period_label,
            'total_btc_earned' => $this->total_btc_earned,
            'average_btc_price' => $this->average_btc_price,
            'total_revenue' => $this->total_revenue,
            'status' => $this->status,
            'client_decision' => $this->client_decision,
            'is_locked' => $this->is_locked,
            'is_eligible_for_request' => $this->isEligibleForRequest(),
            'requested_at' => $this->requested_at,
            'processed_at' => $this->processed_at,
            'notes' => $this->notes,
            'earnings' => EarningResource::collection($this->whenLoaded('earnings')),
            'transaction' => TransactionResource::make($this->whenLoaded('transaction')),
            'created_at' => $this->created_at,
        ];
    }
}
