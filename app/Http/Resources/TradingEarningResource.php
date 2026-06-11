<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradingEarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'trading_contract_id' => $this->trading_contract_id,
            'trading_period_id' => $this->trading_period_id,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'notes' => $this->notes,
            'trading_contract' => TradingContractResource::make($this->whenLoaded('tradingContract')),
            'trading_period' => TradingPeriodResource::make($this->whenLoaded('tradingPeriod')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
