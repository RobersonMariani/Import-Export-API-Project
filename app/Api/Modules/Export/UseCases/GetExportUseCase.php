<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Models\Export;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(string $exportId, int $userId): Export
    {
        $export = $this->exportRepository->findByIdForUser($exportId, $userId);

        if ($export === null) {
            throw new ModelNotFoundException('Export não encontrado.');
        }

        return $export;
    }
}
