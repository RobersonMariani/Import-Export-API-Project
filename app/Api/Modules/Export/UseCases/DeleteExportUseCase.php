<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Repositories\ExportRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class DeleteExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(string $id, int $userId): void
    {
        $export = $this->exportRepository->findByIdForUser($id, $userId);

        if ($export === null) {
            throw new ModelNotFoundException('Export não encontrado.');
        }

        if ($export->file_path && Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }

        $export->delete();
    }
}
