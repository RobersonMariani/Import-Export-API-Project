<?php

declare(strict_types=1);

namespace App\Api\Modules\User\UseCases;

use App\Api\Modules\User\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $user = $this->repository->findById($id);

            if ($user === null) {
                throw new ModelNotFoundException;
            }

            $this->repository->delete($user);
        });
    }
}
