<?php

namespace App\Api\Modules\User\UseCases;

use App\Api\Modules\User\Data\UpdateUserData;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function execute(int $id, UpdateUserData $data): User
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new ModelNotFoundException;
        }

        return DB::transaction(fn () => $this->repository->update($user, $data->toArrayModel()));
    }
}
