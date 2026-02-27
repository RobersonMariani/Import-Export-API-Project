<?php

namespace App\Api\Modules\Auth\UseCases;

use App\Api\Modules\Auth\Services\AuthService;

class LogoutAuthUseCase
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function execute(): void
    {
        $this->authService->logout();
    }
}
