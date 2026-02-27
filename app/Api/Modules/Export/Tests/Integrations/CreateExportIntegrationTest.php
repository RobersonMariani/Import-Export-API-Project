<?php

namespace App\Api\Modules\Export\Tests\Integrations;

use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Api\Modules\Export\Tests\Assertables\ExportAssertableJson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class CreateExportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/exports';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    public function testShouldReturnAcceptedWhenPayloadIsValid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $payload = [];

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, $payload)
            ->assertStatus(202)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    ExportAssertableJson::schema($json);
                })->etc();
            });

        Bus::assertDispatched(ProcessExportJob::class);
    }

    public function testShouldReturnAcceptedWhenPayloadIsValidWithFilters(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $payload = [
            'filters' => ['search' => 'test', 'role' => 'admin'],
            'compressed' => true,
        ];

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, $payload)
            ->assertStatus(202)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    ExportAssertableJson::schema($json);
                })->etc();
            });

        Bus::assertDispatched(ProcessExportJob::class);
    }

    public function testShouldReturnUnprocessableWhenFiltersInvalid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $payload = ['filters' => ['role' => 'invalid_role']];

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->postJson(self::ENDPOINT, [])
            ->assertUnauthorized();
    }
}
