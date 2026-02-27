<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\UseCases;

use App\Api\Modules\Auth\Data\LoginAuthData;
use App\Api\Modules\Auth\Services\AuthService;
use App\Api\Modules\Auth\UseCases\LoginAuthUseCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class LoginAuthUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnTokenAndExpiresInWhenCredentialsAreValid(): void
    {
        // Arrange
        $data = LoginAuthData::validateAndCreate([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) {
                $mock->shouldReceive('attempt')
                    ->once()
                    ->with(['email' => 'test@example.com', 'password' => 'password'])
                    ->andReturn('jwt-token-123');
                $mock->shouldReceive('getTokenTtl')
                    ->once()
                    ->andReturn(3600);
            }),
        );

        // Act
        $useCase = app()->make(LoginAuthUseCase::class);
        $result = $useCase->execute($data);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertEquals('jwt-token-123', $result['token']);
        $this->assertEquals(3600, $result['expires_in']);
    }

    public function testExecuteShouldReturnNullWhenCredentialsAreInvalid(): void
    {
        // Arrange
        $data = LoginAuthData::validateAndCreate([
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) {
                $mock->shouldReceive('attempt')
                    ->once()
                    ->with(['email' => 'test@example.com', 'password' => 'wrong-password'])
                    ->andReturn(null);
                $mock->shouldNotReceive('getTokenTtl');
            }),
        );

        // Act
        $useCase = app()->make(LoginAuthUseCase::class);
        $result = $useCase->execute($data);

        // Assert
        $this->assertNull($result);
    }
}
