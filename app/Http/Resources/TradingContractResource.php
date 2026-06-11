<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradingContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'amount' => (float) $this->amount,
            'earning' => (float) $this->earning,
            'roi_percent' => $this->roi_percent,
            'file_url' => $this->file ? asset('storage/' . $this->file) : null,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'period_label' => $this->period_label,
            'status' => $this->status,
            'notes' => $this->notes,
            'trading_periods_count' => $this->whenCounted('tradingPeriods'),
            'trading_earnings_count' => $this->whenCounted('tradingEarnings'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
