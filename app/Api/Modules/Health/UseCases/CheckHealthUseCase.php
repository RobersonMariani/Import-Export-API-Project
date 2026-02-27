<?php

namespace App\Api\Modules\Health\UseCases;

use App\Api\Modules\Health\Services\HealthCheckService;

class CheckHealthUseCase
{
    public function __construct(
        private readonly HealthCheckService $healthCheckService,
    ) {}

    /**
     * @return array{status: \App\Api\Modules\Health\Enums\HealthStatusEnum, services: array<int, array{name: string, status: string, latency_ms: int|null}>, timestamp: \Illuminate\Support\Carbon}
     */
    public function execute(): array
    {
        return $this->healthCheckService->check();
    }
}
