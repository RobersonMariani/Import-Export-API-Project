<?php

namespace App\Api\Modules\Auth\Tests\UseCases;

use App\Api\Modules\Auth\Services\AuthService;
use App\Api\Modules\Auth\UseCases\LogoutAuthUseCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class LogoutAuthUseCaseTest extends TestCase
{
    public function testExecuteShouldCallLogoutOnAuthServiceWhenCalled(): void
    {
        // Arrange
        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) {
                $mock->shouldReceive('logout')
                    ->once();
            }),
        );

        // Act
        $useCase = app()->make(LogoutAuthUseCase::class);
        $useCase->execute();

        // Assert — mock validates logout() was called once
    }
}
