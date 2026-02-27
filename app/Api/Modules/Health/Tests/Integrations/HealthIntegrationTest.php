<?php

declare(strict_types=1);

namespace App\Api\Modules\Health\Tests\Integrations;

use App\Api\Modules\Health\Tests\Assertables\HealthAssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('health')]
class HealthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const HEALTH_ENDPOINT = '/api/v1/health';

    private const METRICS_ENDPOINT = '/api/v1/metrics';

    public function testShouldReturnOkWhenHealthEndpointIsCalledWithoutAuth(): void
    {
        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::HEALTH_ENDPOINT)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    HealthAssertableJson::schema($json);
                })->etc();
            });
    }

    public function testShouldReturnTextPlainWhenMetricsEndpointIsCalledWithoutAuth(): void
    {
        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'text/plain')
            ->get(self::METRICS_ENDPOINT);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8; version=0.0.4');
        $this->assertStringContainsString('app_up 1', $response->getContent());
        $this->assertStringContainsString('import_total', $response->getContent());
        $this->assertStringContainsString('export_total', $response->getContent());
        $this->assertStringContainsString('queue_size', $response->getContent());
    }

    public function testHealthShouldNotRequireAuthentication(): void
    {
        // Act & Assert - sem Authorization header
        $this
            ->getJson(self::HEALTH_ENDPOINT)
            ->assertOk();
    }

    public function testMetricsShouldNotRequireAuthentication(): void
    {
        // Act & Assert - sem Authorization header
        $this
            ->get(self::METRICS_ENDPOINT)
            ->assertOk();
    }
}
