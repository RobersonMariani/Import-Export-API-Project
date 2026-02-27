<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\Integrations;

use App\Api\Modules\Auth\Tests\Assertables\AuthTokenAssertableJson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class LoginAuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/auth/login';

    public function testShouldReturnOkWithTokenWhenCredentialsAreValid(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->postJson(self::ENDPOINT, $payload)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    AuthTokenAssertableJson::schema($json);
                });
            });
    }

    public function testShouldReturnUnauthorizedWhenCredentialsAreInvalid(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ];

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function testShouldReturnUnprocessableWhenRequiredFieldsMissing(): void
    {
        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->postJson(self::ENDPOINT, [])
            ->assertUnprocessable();
    }
}
