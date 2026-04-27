<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'earning_period_id' => $this->earning_period_id,
            'date' => $this->date?->format('Y-m-d'),
            'btc_earned' => $this->btc_earned,
            'btc_price' => $this->btc_price,
            'revenue' => $this->revenue,
            'additional_notes' => $this->additional_notes,
            'earning_period' => EarningPeriodResource::make($this->whenLoaded('earningPeriod')),
            'created_at' => $this->created_at,
        ];
    }
}
