<?php

declare(strict_types=1);

namespace App\Api\Modules\Health\UseCases;

use App\Api\Modules\Health\Enums\HealthStatusEnum;
use App\Api\Modules\Health\Services\HealthCheckService;
use Illuminate\Support\Carbon;

class CheckHealthUseCase
{
    public function __construct(
        private readonly HealthCheckService $healthCheckService,
    ) {}

    /**
     * @return array{status: HealthStatusEnum, services: array<int, array{name: string, status: string, latency_ms: int|null}>, timestamp: Carbon}
     */
    public function execute(): array
    {
        return $this->healthCheckService->check();
    }
}
