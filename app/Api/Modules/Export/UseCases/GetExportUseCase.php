<?php

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Models\Export;

class GetExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(string $exportId, int $userId): Export
    {
        $export = $this->exportRepository->findByIdForUser($exportId, $userId);

        if ($export === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Export não encontrado.');
        }

        return $export;
    }
}
