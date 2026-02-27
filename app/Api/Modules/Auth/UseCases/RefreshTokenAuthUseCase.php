<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\UseCases;

use App\Api\Modules\Auth\Services\AuthService;

class RefreshTokenAuthUseCase
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /** @return array{token: string, expires_in: int} */
    public function execute(): array
    {
        $token = $this->authService->refresh();

        return [
            'token' => $token,
            'expires_in' => $this->authService->getTokenTtl(),
        ];
    }
}
