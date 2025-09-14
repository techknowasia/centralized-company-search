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
            'phone' => $this->phone ?? null,
            'email' => $this->email ?? null,
            'website' => $this->website ?? null,
            'country' => $this->country ?? null,
            'state_id' => $this->state_id ?? null,
            'created_at' => property_exists($this, 'created_at')
                ? ($this->created_at instanceof \DateTimeInterface ? $this->created_at->toISOString() : $this->created_at)
                : null,
            'updated_at' => property_exists($this, 'updated_at')
                ? ($this->updated_at instanceof \DateTimeInterface ? $this->updated_at->toISOString() : $this->updated_at)
                : null,
        ];
    }
}
