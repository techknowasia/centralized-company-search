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
            'report_states' => $this->when($this->relationLoaded('reportStates'), function () {
                return $this->reportStates->map(function ($reportState) {
                    return [
                        'id' => $reportState->id,
                        'state_id' => $reportState->state_id,
                        'amount' => $reportState->amount
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
