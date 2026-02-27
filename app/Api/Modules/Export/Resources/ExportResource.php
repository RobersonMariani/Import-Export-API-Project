<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Resources;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Export */
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
            'expires_at' => $this->formatDate($export->expires_at),
            'started_at' => $this->formatDate($export->started_at),
            'finished_at' => $this->formatDate($export->finished_at),
            'processing_time_seconds' => $processingTime,
            'created_at' => $this->formatDate($export->created_at),
        ];
    }

    private function processingTimeSeconds(Export $export): ?int
    {
        $startedAt = $export->started_at;
        if ($startedAt === null) {
            return null;
        }

        $end = $export->finished_at ?? now();
        $startedCarbon = Carbon::parse($startedAt);

        return (int) $startedCarbon->diffInSeconds($end);
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof \Carbon\CarbonInterface ? $value->toIso8601String() : null;
    }
}
