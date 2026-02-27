<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\Services\ExportService;
use App\Models\Export;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class DownloadExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
        private readonly ExportService $exportService,
    ) {}

    public function execute(string $exportId, int $userId): Export
    {
        $export = $this->exportRepository->findByIdForUser($exportId, $userId);

        if ($export === null) {
            throw new ModelNotFoundException('Export não encontrado.');
        }

        if ($export->status !== ExportStatusEnum::Completed->value) {
            throw new RuntimeException('Export ainda não está disponível para download.');
        }

        if ($export->file_path === null) {
            throw new RuntimeException('Arquivo de export não encontrado.');
        }

        return $export;
    }

    public function getDownloadUrl(Export $export): string
    {
        return $this->exportService->getTemporaryDownloadUrl($export);
    }
}
