<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\UseCases;

use App\Api\Modules\Auth\Data\RegisterAuthData;
use App\Api\Modules\Auth\Repositories\UserRepository;
use App\Api\Modules\Auth\Services\AuthService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterAuthUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthService $authService,
    ) {}

    /** @return array{user: User, token: string, expires_in: int} */
    public function execute(RegisterAuthData $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create($data->toArrayModel());
            $token = $this->authService->loginUser($user);

            return [
                'user' => $user,
                'token' => $token,
                'expires_in' => $this->authService->getTokenTtl(),
            ];
        });
    }
}
