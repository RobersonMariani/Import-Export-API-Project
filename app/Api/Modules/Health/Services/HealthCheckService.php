<?php

namespace App\Api\Modules\Health\Services;

use App\Api\Modules\Health\Enums\HealthStatusEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class HealthCheckService
{
    private const DEGRADED_LATENCY_MS = 1000;

    private const UNHEALTHY_LATENCY_MS = 5000;

    /**
     * @return array{status: HealthStatusEnum, services: array<int, array{name: string, status: string, latency_ms: int|null}>, timestamp: \Illuminate\Support\Carbon}
     */
    public function check(): array
    {
        $services = [
            $this->checkPostgresql(),
            $this->checkRedis(),
            $this->checkStorage(),
            $this->checkQueue(),
        ];

        $status = $this->determineOverallStatus($services);
        $timestamp = now();

        return [
            'status' => $status,
            'services' => $services,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * @return array{name: string, status: string, latency_ms: int|null}
     */
    private function checkPostgresql(): array
    {
        $start = $this->microtimeMs();

        try {
            DB::connection()->getPdo();
            $latency = $this->elapsedMs($start);

            return [
                'name' => 'postgresql',
                'status' => $this->serviceStatus($latency),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable) {
            return [
                'name' => 'postgresql',
                'status' => HealthStatusEnum::Unhealthy->value,
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{name: string, status: string, latency_ms: int|null}
     */
    private function checkRedis(): array
    {
        $start = $this->microtimeMs();

        try {
            Cache::store('redis')->get('health');
            $latency = $this->elapsedMs($start);

            return [
                'name' => 'redis',
                'status' => $this->serviceStatus($latency),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable) {
            return [
                'name' => 'redis',
                'status' => HealthStatusEnum::Unhealthy->value,
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{name: string, status: string, latency_ms: int|null}
     */
    private function checkStorage(): array
    {
        $start = $this->microtimeMs();

        try {
            Storage::disk()->exists('.');
            $latency = $this->elapsedMs($start);

            return [
                'name' => 'storage',
                'status' => $this->serviceStatus($latency),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable) {
            return [
                'name' => 'storage',
                'status' => HealthStatusEnum::Unhealthy->value,
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{name: string, status: string, latency_ms: int|null}
     */
    private function checkQueue(): array
    {
        $start = $this->microtimeMs();

        try {
            Queue::connection()->size('default');
            $latency = $this->elapsedMs($start);

            return [
                'name' => 'queue',
                'status' => $this->serviceStatus($latency),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable) {
            return [
                'name' => 'queue',
                'status' => HealthStatusEnum::Unhealthy->value,
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @param  array<int, array{name: string, status: string, latency_ms: int|null}>  $services
     */
    private function determineOverallStatus(array $services): HealthStatusEnum
    {
        $hasUnhealthy = false;
        $hasDegraded = false;

        foreach ($services as $service) {
            if ($service['status'] === HealthStatusEnum::Unhealthy->value) {
                $hasUnhealthy = true;
            }
            if ($service['status'] === HealthStatusEnum::Degraded->value) {
                $hasDegraded = true;
            }
        }

        if ($hasUnhealthy) {
            return HealthStatusEnum::Unhealthy;
        }

        if ($hasDegraded) {
            return HealthStatusEnum::Degraded;
        }

        return HealthStatusEnum::Healthy;
    }

    private function serviceStatus(?int $latencyMs): string
    {
        if ($latencyMs === null) {
            return HealthStatusEnum::Unhealthy->value;
        }

        if ($latencyMs >= self::UNHEALTHY_LATENCY_MS) {
            return HealthStatusEnum::Unhealthy->value;
        }

        if ($latencyMs >= self::DEGRADED_LATENCY_MS) {
            return HealthStatusEnum::Degraded->value;
        }

        return HealthStatusEnum::Healthy->value;
    }

    private function microtimeMs(): float
    {
        return microtime(true) * 1000;
    }

    private function elapsedMs(float $start): int
    {
        return (int) round($this->microtimeMs() - $start);
    }
}
