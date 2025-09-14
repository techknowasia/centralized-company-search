<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDetailResource extends JsonResource
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
            'country_name' => $this->country_name ?? null,
            'state_id' => $this->state_id ?? null,
            'state_name' => $this->state_name ?? null,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at)
        ];
    }

    /**
     * Format date (works with both Carbon instances and strings)
     */
    private function formatDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        // If it's a Carbon instance
        if (method_exists($date, 'toISOString')) {
            return $date->toISOString();
        }

        // If it's a string, try to parse it
        if (is_string($date)) {
            try {
                return \Carbon\Carbon::parse($date)->toISOString();
            } catch (\Exception $e) {
                return $date;
            }
        }

        return null;
    }
}
