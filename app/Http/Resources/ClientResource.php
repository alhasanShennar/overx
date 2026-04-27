<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->phone,
            'passport' => $this->passport,
            'current_storing_machines' => $this->current_storing_machines,
            'current_cashout_machines' => $this->current_cashout_machines,
            'total_machines' => $this->current_storing_machines + $this->current_cashout_machines,
            'stored_balance_btc' => $this->whenCounted('storedEarnings') ?? null,
            'contracts' => ContractResource::collection($this->whenLoaded('contracts')),
            'cashout_details' => CashoutDetailResource::collection($this->whenLoaded('cashoutDetails')),
            'created_at' => $this->created_at,
        ];
    }
}
