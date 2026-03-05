<?php

declare(strict_types=1);

namespace App\Api\Modules\User\UseCases;

use App\Api\Modules\User\Repositories\UserRepository;

class CountUsersUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /** @param array<string, mixed> $filters */
    public function execute(array $filters = []): int
    {
        return $this->userRepository->countForExport($filters);
    }
}
