<?php

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Data\CreateExportData;
use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Models\Export;
use Illuminate\Support\Facades\DB;

class CreateExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(CreateExportData $data, int $userId): Export
    {
        return DB::transaction(function () use ($data, $userId): Export {
            $export = $this->exportRepository->create([
                'user_id' => $userId,
                'status' => ExportStatusEnum::Queued->value,
                'file_path' => null,
                'filters' => $data->filters?->toArray(),
                'total_records' => 0,
                'compressed' => $data->compressed,
                'expires_at' => null,
                'started_at' => null,
                'finished_at' => null,
            ]);

            ProcessExportJob::dispatch($export->id)->onQueue('exports');

            return $export;
        });
    }
}
