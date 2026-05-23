<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'order'                => $this->order,
            'number'               => $this->number,
            'title'                => $this->title,
            'tagline'              => $this->tagline,
            'card_image'           => $this->card_image ? asset('storage/' . $this->card_image) : null,
            'hero_image'           => $this->hero_image ? asset('storage/' . $this->hero_image) : null,
            'overview_title'       => $this->overview_title,
            'overview_description' => $this->overview_description,
            'overview_image'       => $this->overview_image ? asset('storage/' . $this->overview_image) : null,
            'process_title'        => $this->process_title,
            'process_description'  => $this->process_description,
            'steps'                => collect($this->steps ?? [])->map(fn ($step) => [
                'icon'        => isset($step['icon']) ? asset('storage/' . $step['icon']) : null,
                'title'       => $step['title'] ?? null,
                'description' => $step['description'] ?? null,
            ])->values(),
            'faqs'                 => $this->faqs ?? [],
        ];
    }
}
