<?php

namespace App\Api\Modules\User\UseCases;

use App\Api\Modules\User\Data\UserQueryData;
use App\Api\Modules\User\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUsersUseCase
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function execute(UserQueryData $query): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($query);
    }
}
