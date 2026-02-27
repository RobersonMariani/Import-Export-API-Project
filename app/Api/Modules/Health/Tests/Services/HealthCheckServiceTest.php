<?php

namespace App\Api\Modules\Health\Tests\Services;

use App\Api\Modules\Health\Enums\HealthStatusEnum;
use App\Api\Modules\Health\Services\HealthCheckService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('health')]
class HealthCheckServiceTest extends TestCase
{
    public function testCheckShouldReturnHealthyWhenAllServicesRespond(): void
    {
        // Arrange
        $pdoMock = Mockery::mock(\PDO::class);
        $connectionMock = Mockery::mock(\Illuminate\Database\Connection::class);
        $connectionMock->shouldReceive('getPdo')->andReturn($pdoMock);

        DB::shouldReceive('connection')
            ->andReturn($connectionMock);

        $cacheStore = Mockery::mock(\Illuminate\Contracts\Cache\Store::class);
        $cacheStore->shouldReceive('get')->with('health')->andReturn(null);
        $cacheRepository = new \Illuminate\Cache\Repository($cacheStore);

        Cache::shouldReceive('store')
            ->with('redis')
            ->andReturn($cacheRepository);

        Storage::fake('local');

        $queueConnection = Mockery::mock();
        $queueConnection->shouldReceive('size')->with('default')->andReturn(0);

        Queue::shouldReceive('connection')
            ->andReturn($queueConnection);

        $service = new HealthCheckService();

        // Act
        $result = $service->check();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('services', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertInstanceOf(HealthStatusEnum::class, $result['status']);
        $this->assertCount(4, $result['services']);

        $serviceNames = array_column($result['services'], 'name');
        $this->assertContains('postgresql', $serviceNames);
        $this->assertContains('redis', $serviceNames);
        $this->assertContains('storage', $serviceNames);
        $this->assertContains('queue', $serviceNames);
    }

    public function testCheckShouldReturnUnhealthyWhenDatabaseFails(): void
    {
        // Arrange
        DB::shouldReceive('connection')
            ->andThrow(new \PDOException('Connection refused'));

        $cacheStore = Mockery::mock(\Illuminate\Contracts\Cache\Store::class);
        $cacheStore->shouldReceive('get')->with('health')->andReturn(null);
        $cacheRepository = new \Illuminate\Cache\Repository($cacheStore);

        Cache::shouldReceive('store')
            ->with('redis')
            ->andReturn($cacheRepository);

        Storage::fake('local');

        $queueConnection = Mockery::mock();
        $queueConnection->shouldReceive('size')->with('default')->andReturn(0);

        Queue::shouldReceive('connection')
            ->andReturn($queueConnection);

        $service = new HealthCheckService();

        // Act
        $result = $service->check();

        // Assert
        $this->assertSame(HealthStatusEnum::Unhealthy, $result['status']);
        $postgresql = collect($result['services'])->firstWhere('name', 'postgresql');
        $this->assertNotNull($postgresql);
        $this->assertSame('unhealthy', $postgresql['status']);
        $this->assertNull($postgresql['latency_ms']);
    }

    public function testCheckShouldReturnUnhealthyWhenRedisFails(): void
    {
        // Arrange
        $pdoMock = Mockery::mock(\PDO::class);
        $connectionMock = Mockery::mock(\Illuminate\Database\Connection::class);
        $connectionMock->shouldReceive('getPdo')->andReturn($pdoMock);

        DB::shouldReceive('connection')
            ->andReturn($connectionMock);

        Cache::shouldReceive('store')
            ->with('redis')
            ->andThrow(new \RuntimeException('Connection refused'));

        Storage::fake('local');

        $queueConnection = Mockery::mock();
        $queueConnection->shouldReceive('size')->with('default')->andReturn(0);

        Queue::shouldReceive('connection')
            ->andReturn($queueConnection);

        $service = new HealthCheckService();

        // Act
        $result = $service->check();

        // Assert
        $this->assertSame(HealthStatusEnum::Unhealthy, $result['status']);
        $redis = collect($result['services'])->firstWhere('name', 'redis');
        $this->assertNotNull($redis);
        $this->assertSame('unhealthy', $redis['status']);
        $this->assertNull($redis['latency_ms']);
    }
}
