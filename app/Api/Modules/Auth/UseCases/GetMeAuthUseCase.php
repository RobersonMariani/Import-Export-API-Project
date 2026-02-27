<?php

namespace App\Api\Modules\Auth\UseCases;

use App\Api\Modules\Auth\Services\AuthService;
use App\Models\User;

class GetMeAuthUseCase
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function execute(): ?User
    {
        return $this->authService->user();
    }
}
