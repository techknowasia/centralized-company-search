<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'registration_number' => $this->registration_number ?? null,
            'former_names' => $this->former_names ?? null,
            'brand_name' => $this->brand_name ?? null,
            'address' => $this->address,
            'country' => $this->country ?? 'unknown',
            'country_name' => $this->country_name ?? 'Unknown',
            'state' => $this->when($this->relationLoaded('state'), function () {
                return [
                    'id' => $this->state?->id,
                    'name' => $this->state?->name
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
