<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\UseCases;

use App\Api\Modules\Auth\Data\RegisterAuthData;
use App\Api\Modules\Auth\Repositories\UserRepository;
use App\Api\Modules\Auth\Services\AuthService;
use App\Api\Modules\Auth\UseCases\RegisterAuthUseCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class RegisterAuthUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnUserTokenAndExpiresInWhenDataIsValid(): void
    {
        // Arrange
        $data = RegisterAuthData::validateAndCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('create')
                    ->once()
                    ->andReturn($user);
            }),
        );

        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) {
                $mock->shouldReceive('loginUser')
                    ->once()
                    ->with(Mockery::type(User::class))
                    ->andReturn('jwt-token-123');
                $mock->shouldReceive('getTokenTtl')
                    ->once()
                    ->andReturn(3600);
            }),
        );

        // Act
        $useCase = app()->make(RegisterAuthUseCase::class);
        $result = $useCase->execute($data);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals('jwt-token-123', $result['token']);
        $this->assertEquals(3600, $result['expires_in']);
    }
}
