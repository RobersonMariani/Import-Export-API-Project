<?php

declare(strict_types=1);

namespace App\Api\Modules\User\UseCases;

use App\Api\Modules\User\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetUserUseCase
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function execute(int $id): User
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new ModelNotFoundException;
        }

        return $user;
    }
}
