<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug ?? null,
            'registration_number' => $this->registration_number ?? null,
            'former_names' => $this->former_names ?? null,
            'brand_name' => $this->brand_name ?? null,
            'address' => $this->address ?? null,
            'country' => $this->country ?? 'unknown',
            'country_name' => $this->country_name ?? 'Unknown',
            'state_name' => $this->state_name ?? null,
            'relevance_score' => $this->relevance_score ?? null
        ];
    }
}
