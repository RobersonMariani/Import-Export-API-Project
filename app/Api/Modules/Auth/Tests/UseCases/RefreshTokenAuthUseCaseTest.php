<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\UseCases;

use App\Api\Modules\Auth\Services\AuthService;
use App\Api\Modules\Auth\UseCases\RefreshTokenAuthUseCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class RefreshTokenAuthUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnTokenAndExpiresInWhenCalled(): void
    {
        // Arrange
        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) {
                $mock->shouldReceive('refresh')
                    ->once()
                    ->andReturn('new-jwt-token-456');
                $mock->shouldReceive('getTokenTtl')
                    ->once()
                    ->andReturn(3600);
            }),
        );

        // Act
        $useCase = app()->make(RefreshTokenAuthUseCase::class);
        $result = $useCase->execute();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertEquals('new-jwt-token-456', $result['token']);
        $this->assertEquals(3600, $result['expires_in']);
    }
}
