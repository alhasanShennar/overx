<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->when(
                $this->isSuperAdmin(),
                'super_admin',
                $this->when(
                    $this->isAdmin(),
                    'admin',
                    $this->when($this->isClient(), 'client', null)
                )
            ),
            'client' => ClientResource::make($this->whenLoaded('client')),
            'created_at' => $this->created_at,
        ];
    }
}
