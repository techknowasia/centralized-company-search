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
            'slug' => $this->slug ?? null,
            'registration_number' => $this->registration_number ?? null,
            'former_names' => $this->former_names ?? null,
            'brand_name' => $this->brand_name ?? null,
            'address' => $this->address ?? null,
            'country' => $this->country ?? 'unknown',
            'country_name' => $this->country_name ?? 'Unknown',
            'state_name' => $this->state_name ?? null,
            'state' => $this->when(isset($this->state) && $this->state, function () {
                return [
                    'id' => $this->state?->id ?? null,
                    'name' => $this->state?->name ?? null
                ];
            }),
            // Only include timestamps if they exist (for Eloquent models)
            'created_at' => $this->when(property_exists($this->resource, 'created_at'), function () {
                return $this->created_at?->toISOString();
            }),
            'updated_at' => $this->when(property_exists($this->resource, 'updated_at'), function () {
                return $this->updated_at?->toISOString();
            })
        ];
    }
}
