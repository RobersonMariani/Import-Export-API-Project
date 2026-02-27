<?php

namespace App\Api\Modules\Export\Resources;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Export $export */
        $export = $this->resource;

        $statusEnum = $export->status ? ExportStatusEnum::tryFrom($export->status) : null;
        $processingTime = $this->processingTimeSeconds($export);

        return [
            'id' => $export->id,
            'status' => $export->status,
            'status_label' => $statusEnum?->label(),
            'total_records' => $export->total_records ?? 0,
            'compressed' => $export->compressed ?? false,
            'file_path' => $export->status === ExportStatusEnum::Completed->value ? $export->file_path : null,
            'download_url' => $this->additional['download_url'] ?? null,
            'expires_at' => $export->expires_at?->toIso8601String(),
            'started_at' => $export->started_at?->toIso8601String(),
            'finished_at' => $export->finished_at?->toIso8601String(),
            'processing_time_seconds' => $processingTime,
            'created_at' => $export->created_at?->toIso8601String(),
        ];
    }

    private function processingTimeSeconds(Export $export): ?int
    {
        if ($export->started_at === null) {
            return null;
        }

        $end = $export->finished_at ?? now();

        return (int) $export->started_at->diffInSeconds($end);
    }
}
