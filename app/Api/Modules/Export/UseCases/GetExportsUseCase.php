<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Data\ExportQueryData;
use App\Api\Modules\Export\Repositories\ExportRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetExportsUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(ExportQueryData $query, ?int $userId = null): LengthAwarePaginator
    {
        return $this->exportRepository->getAllPaginated($query, $userId);
    }
}
