<?php

namespace App\Api\Modules\Import\UseCases;

use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Models\Import;

class GetImportUseCase
{
    public function __construct(
        private readonly ImportRepository $importRepository,
    ) {}

    public function execute(string $id, ?int $userId = null): Import
    {
        $import = $this->importRepository->findById($id);

        if ($import === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Import não encontrado.');
        }

        if ($userId !== null && $import->user_id !== $userId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Import não encontrado.');
        }

        return $import;
    }
}
