<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\UseCases;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\Services\ImportService;
use App\Models\Import;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class RetryImportUseCase
{
    public function __construct(
        private readonly ImportRepository $importRepository,
        private readonly ImportService $importService,
    ) {}

    public function execute(string $id, int $userId): Import
    {
        $import = $this->importRepository->findByIdForUser($id, $userId);

        if ($import === null) {
            throw new ModelNotFoundException('Import não encontrado.');
        }

        if (! in_array($import->status, [ImportStatusEnum::Failed->value, ImportStatusEnum::Partial->value], true)) {
            throw new RuntimeException('Apenas importações com falha podem ser reprocessadas.');
        }

        $this->importRepository->update($import, [
            'status' => ImportStatusEnum::Queued->value,
            'progress' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'total_records' => 0,
            'error_message' => null,
            'started_at' => null,
            'finished_at' => null,
        ]);

        $import->failures()->delete();

        $this->importService->startImport($import->refresh());

        return $import->refresh();
    }
}
