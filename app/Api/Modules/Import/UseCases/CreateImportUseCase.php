<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\UseCases;

use App\Api\Modules\Import\Data\CreateImportData;
use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\Services\ImportService;
use App\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CreateImportUseCase
{
    public function __construct(
        private readonly ImportRepository $importRepository,
        private readonly ImportService $importService,
    ) {}

    public function execute(CreateImportData $data, int $userId): Import
    {
        if ($userId <= 0) {
            throw new RuntimeException('Usuário não autenticado.');
        }

        $import = DB::transaction(function () use ($data, $userId): Import {
            $path = $data->file->store('imports', 'local');
            $originalFilename = $data->file->getClientOriginalName();

            return $this->importRepository->create([
                'user_id' => $userId,
                'status' => ImportStatusEnum::Queued->value,
                'progress' => 0,
                'total_records' => 0,
                'success_count' => 0,
                'failure_count' => 0,
                'file_path' => $path,
                'original_filename' => $originalFilename,
                'metadata' => null,
            ]);
        });

        $this->dispatchWithRetry($import);

        return $import;
    }

    private function dispatchWithRetry(Import $import, int $maxRetries = 3): void
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $this->importService->startImport($import);

                return;
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning('Import dispatch attempt failed', [
                    'import_id' => $import->id,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $maxRetries) {
                    usleep($attempt * 100_000);
                }
            }
        }

        $this->importRepository->update($import, [
            'status' => ImportStatusEnum::Failed->value,
            'error_message' => 'Falha ao despachar job após '.$maxRetries.' tentativas: '.$lastException?->getMessage(),
            'finished_at' => now(),
        ]);

        Log::error('Import dispatch failed permanently', [
            'import_id' => $import->id,
            'error' => $lastException?->getMessage(),
        ]);
    }
}
