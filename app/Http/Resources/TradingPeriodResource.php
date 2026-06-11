<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradingPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'trading_contract_id' => $this->trading_contract_id,
            'period' => $this->period_label,
            'year' => $this->year,
            'month' => $this->month,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'total_earning' => (float) $this->total_earning,
            'status' => $this->status,
            'client_decision' => $this->client_decision,
            'is_locked' => $this->is_locked,
            'is_eligible_for_decision' => $this->isEligibleForDecision(),
            'requested_at' => $this->requested_at?->toIso8601String(),
            'processed_at' => $this->processed_at?->toIso8601String(),
            'notes' => $this->notes,
            'trading_contract' => TradingContractResource::make($this->whenLoaded('tradingContract')),
            'trading_earnings' => TradingEarningResource::collection($this->whenLoaded('tradingEarnings')),
            'transaction' => TradingTransactionResource::make($this->whenLoaded('transaction')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
