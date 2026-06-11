<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradingTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trading_contract_id' => $this->trading_contract_id,
            'trading_period_id' => $this->trading_period_id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'processed_at' => $this->processed_at?->toIso8601String(),
            'notes' => $this->notes,
            'trading_period' => TradingPeriodResource::make($this->whenLoaded('tradingPeriod')),
            'trading_cashout' => TradingCashoutResource::make($this->whenLoaded('tradingCashout')),
            'trading_stored_earning' => TradingStoredEarningResource::make($this->whenLoaded('tradingStoredEarning')),
        ];
    }
}
