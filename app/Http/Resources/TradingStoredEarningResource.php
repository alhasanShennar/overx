<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradingStoredEarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trading_contract_id' => $this->trading_contract_id,
            'trading_period_id' => $this->trading_period_id,
            'amount' => (float) $this->amount,
            'stored_at' => $this->stored_at?->toIso8601String(),
            'notes' => $this->notes,
            'trading_contract' => TradingContractResource::make($this->whenLoaded('tradingContract')),
            'trading_period' => TradingPeriodResource::make($this->whenLoaded('tradingPeriod')),
        ];
    }
}
