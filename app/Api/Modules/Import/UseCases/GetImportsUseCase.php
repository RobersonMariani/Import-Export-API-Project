<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\UseCases;

use App\Api\Modules\Import\Data\ImportQueryData;
use App\Api\Modules\Import\Repositories\ImportRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetImportsUseCase
{
    public function __construct(
        private readonly ImportRepository $importRepository,
    ) {}

    public function execute(ImportQueryData $query, ?int $userId = null): LengthAwarePaginator
    {
        return $this->importRepository->getAllPaginated($query, $userId);
    }
}
