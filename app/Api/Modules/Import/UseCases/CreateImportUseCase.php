<?php

namespace App\Api\Modules\Import\UseCases;

use App\Api\Modules\Import\Data\CreateImportData;
use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\Services\ImportService;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class CreateImportUseCase
{
    public function __construct(
        private readonly ImportRepository $importRepository,
        private readonly ImportService $importService,
    ) {}

    public function execute(CreateImportData $data, int $userId): Import
    {
        if ($userId <= 0) {
            throw new \RuntimeException('Usuário não autenticado.');
        }

        return DB::transaction(function () use ($data, $userId): Import {
            $path = $data->file->store('imports', 'local');
            $originalFilename = $data->file->getClientOriginalName();

            $import = $this->importRepository->create([
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

            $this->importService->startImport($import);

            return $import;
        });
    }
}
