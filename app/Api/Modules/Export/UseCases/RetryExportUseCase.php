<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Models\Export;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class RetryExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(string $id, int $userId): Export
    {
        $export = $this->exportRepository->findByIdForUser($id, $userId);

        if ($export === null) {
            throw new ModelNotFoundException('Export não encontrado.');
        }

        if ($export->status !== ExportStatusEnum::Failed->value) {
            throw new RuntimeException('Apenas exportações com falha podem ser reprocessadas.');
        }

        if ($export->file_path && Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }

        $this->exportRepository->update($export, [
            'status' => ExportStatusEnum::Queued->value,
            'file_path' => null,
            'total_records' => 0,
            'error_message' => null,
            'expires_at' => null,
            'started_at' => null,
            'finished_at' => null,
        ]);

        ProcessExportJob::dispatch($export->id)->onQueue('exports');

        return $export->refresh();
    }
}
