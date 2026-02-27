<?php

namespace App\Api\Modules\Import\Resources;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Import $import */
        $import = $this->resource;

        $statusEnum = $import->status ? ImportStatusEnum::tryFrom($import->status) : null;
        $processingTime = $this->processingTimeSeconds($import);
        $estimatedRemaining = $this->estimatedRemainingSeconds($import);

        return [
            'id' => $import->id,
            'status' => $import->status,
            'status_label' => $statusEnum?->label(),
            'progress' => $import->progress ?? 0,
            'total_records' => $import->total_records ?? 0,
            'success_count' => $import->success_count ?? 0,
            'failure_count' => $import->failure_count ?? 0,
            'original_filename' => $import->original_filename,
            'started_at' => $import->started_at?->toIso8601String(),
            'finished_at' => $import->finished_at?->toIso8601String(),
            'processing_time_seconds' => $processingTime,
            'estimated_remaining_seconds' => $estimatedRemaining,
            'created_at' => $import->created_at?->toIso8601String(),
        ];
    }

    private function processingTimeSeconds(Import $import): ?int
    {
        if ($import->started_at === null) {
            return null;
        }

        $end = $import->finished_at ?? now();

        return (int) $import->started_at->diffInSeconds($end);
    }

    private function estimatedRemainingSeconds(Import $import): ?int
    {
        $totalRecords = $import->total_records ?? 0;
        $progress = $import->progress ?? 0;

        if (
            $totalRecords <= 0
            || $progress >= $totalRecords
            || $import->started_at === null
        ) {
            return null;
        }

        $elapsed = $import->started_at->diffInSeconds(now());
        $recordsPerSecond = $progress > 0 ? $progress / $elapsed : 0;

        if ($recordsPerSecond <= 0) {
            return null;
        }

        $remaining = $totalRecords - $progress;

        return (int) ceil($remaining / $recordsPerSecond);
    }
}
