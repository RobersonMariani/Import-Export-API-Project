<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\UseCases;

use App\Api\Modules\Import\Repositories\ImportRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class DeleteImportUseCase
{
    public function __construct(
        private readonly ImportRepository $importRepository,
    ) {}

    public function execute(string $id, int $userId): void
    {
        $import = $this->importRepository->findByIdForUser($id, $userId);

        if ($import === null) {
            throw new ModelNotFoundException('Import não encontrado.');
        }

        if ($import->file_path && Storage::exists($import->file_path)) {
            Storage::delete($import->file_path);
        }

        $import->failures()->delete();
        $import->delete();
    }
}
