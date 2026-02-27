<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\Integrations;

use App\Api\Modules\User\Tests\Assertables\UserAssertableJson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class CreateUserIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/users';

    private function validPayload(): array
    {
        return [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ];
    }

    public function testShouldReturnCreatedWhenDataIsValid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $payload = $this->validPayload();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    UserAssertableJson::schema($json);
                })->etc();
            });
    }

    public function testShouldReturnCreatedWithOptionalFieldsWhenProvided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $payload = array_merge($this->validPayload(), [
            'email' => 'fulluser@example.com',
            'phone' => '11999999999',
            'address' => 'Rua Teste, 123',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01234-567',
            'birth_date' => '1990-01-15',
            'role' => 'admin',
        ]);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated();

        $response->assertJsonPath('data.name', 'New User');
        $response->assertJsonPath('data.email', 'fulluser@example.com');
        $response->assertJsonPath('data.role', 'admin');
    }

    public function testShouldReturnUnprocessableWhenRequiredFieldsMissing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, [])
            ->assertUnprocessable();
    }

    public function testShouldReturnUnprocessableWhenEmailAlreadyExists(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        User::factory()->create(['email' => 'existing@example.com']);
        $payload = array_merge($this->validPayload(), ['email' => 'existing@example.com']);

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
            ->postJson(self::ENDPOINT, $this->validPayload())
            ->assertUnauthorized();
    }
}
