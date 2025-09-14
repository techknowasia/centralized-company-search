<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->info ?? $this->description ?? null,
            'price' => $this->amount ?? null,
            'category' => $this->category ?? null,
            'is_active' => $this->is_active ?? null,
            'order' => $this->order ?? null,
            'default' => $this->default ?? null,
            'status' => $this->status ?? null,
            'report_states' => $this->when(
                $this->hasRelationLoaded('reportStates'), 
                function () {
                    return $this->reportStates->map(function ($reportState) {
                        return [
                            'id' => $reportState->id,
                            'state_id' => $reportState->state_id,
                            'amount' => $reportState->amount
                        ];
                    });
                }
            ),
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at)
        ];
    }

    /**
     * Check if relation is loaded (works with both Eloquent models and stdClass objects)
     */
    private function hasRelationLoaded(string $relation): bool
    {
        // For Eloquent models
        if (method_exists($this->resource, 'relationLoaded')) {
            return $this->resource->relationLoaded($relation);
        }
        
        // For stdClass objects, check if the property exists
        return isset($this->resource->{$relation});
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
