<?php

namespace App\Api\Modules\User\UseCases;

use App\Api\Modules\User\Data\CreateUserData;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function execute(CreateUserData $data): User
    {
        return DB::transaction(fn () => $this->repository->create($data->toArrayModel()));
    }
}
