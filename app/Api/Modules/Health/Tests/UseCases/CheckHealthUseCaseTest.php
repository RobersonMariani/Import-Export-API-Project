<?php

namespace App\Api\Modules\Health\Tests\UseCases;

use App\Api\Modules\Health\Enums\HealthStatusEnum;
use App\Api\Modules\Health\Services\HealthCheckService;
use App\Api\Modules\Health\UseCases\CheckHealthUseCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('health')]
class CheckHealthUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnHealthDataWhenServiceReturnsSuccess(): void
    {
        // Arrange
        $expectedResult = [
            'status' => HealthStatusEnum::Healthy,
            'services' => [
                ['name' => 'postgresql', 'status' => 'healthy', 'latency_ms' => 5],
                ['name' => 'redis', 'status' => 'healthy', 'latency_ms' => 2],
                ['name' => 'storage', 'status' => 'healthy', 'latency_ms' => 1],
                ['name' => 'queue', 'status' => 'healthy', 'latency_ms' => 3],
            ],
            'timestamp' => now(),
        ];

        $this->instance(
            HealthCheckService::class,
            Mockery::mock(HealthCheckService::class, function (MockInterface $mock) use ($expectedResult) {
                $mock->shouldReceive('check')
                    ->once()
                    ->andReturn($expectedResult);
            }),
        );

        // Act
        $useCase = app()->make(CheckHealthUseCase::class);
        $result = $useCase->execute();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('services', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertSame(HealthStatusEnum::Healthy, $result['status']);
        $this->assertCount(4, $result['services']);
        $this->assertSame($expectedResult, $result);
    }

    public function testExecuteShouldReturnDegradedStatusWhenServiceReturnsDegraded(): void
    {
        // Arrange
        $expectedResult = [
            'status' => HealthStatusEnum::Degraded,
            'services' => [
                ['name' => 'postgresql', 'status' => 'healthy', 'latency_ms' => 5],
                ['name' => 'redis', 'status' => 'degraded', 'latency_ms' => 1500],
                ['name' => 'storage', 'status' => 'healthy', 'latency_ms' => 1],
                ['name' => 'queue', 'status' => 'healthy', 'latency_ms' => 3],
            ],
            'timestamp' => now(),
        ];

        $this->instance(
            HealthCheckService::class,
            Mockery::mock(HealthCheckService::class, function (MockInterface $mock) use ($expectedResult) {
                $mock->shouldReceive('check')
                    ->once()
                    ->andReturn($expectedResult);
            }),
        );

        // Act
        $useCase = app()->make(CheckHealthUseCase::class);
        $result = $useCase->execute();

        // Assert
        $this->assertSame(HealthStatusEnum::Degraded, $result['status']);
    }

    public function testExecuteShouldReturnUnhealthyStatusWhenServiceReturnsUnhealthy(): void
    {
        // Arrange
        $expectedResult = [
            'status' => HealthStatusEnum::Unhealthy,
            'services' => [
                ['name' => 'postgresql', 'status' => 'unhealthy', 'latency_ms' => null],
                ['name' => 'redis', 'status' => 'healthy', 'latency_ms' => 2],
                ['name' => 'storage', 'status' => 'healthy', 'latency_ms' => 1],
                ['name' => 'queue', 'status' => 'healthy', 'latency_ms' => 3],
            ],
            'timestamp' => now(),
        ];

        $this->instance(
            HealthCheckService::class,
            Mockery::mock(HealthCheckService::class, function (MockInterface $mock) use ($expectedResult) {
                $mock->shouldReceive('check')
                    ->once()
                    ->andReturn($expectedResult);
            }),
        );

        // Act
        $useCase = app()->make(CheckHealthUseCase::class);
        $result = $useCase->execute();

        // Assert
        $this->assertSame(HealthStatusEnum::Unhealthy, $result['status']);
    }
}
