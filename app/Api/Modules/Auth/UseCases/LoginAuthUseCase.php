<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\UseCases;

use App\Api\Modules\Auth\Data\LoginAuthData;
use App\Api\Modules\Auth\Services\AuthService;

class LoginAuthUseCase
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /** @return array{token: string, expires_in: int}|null */
    public function execute(LoginAuthData $data): ?array
    {
        $token = $this->authService->attempt($data->toCredentials());

        if ($token === null) {
            return null;
        }

        return [
            'token' => $token,
            'expires_in' => $this->authService->getTokenTtl(),
        ];
    }
}
