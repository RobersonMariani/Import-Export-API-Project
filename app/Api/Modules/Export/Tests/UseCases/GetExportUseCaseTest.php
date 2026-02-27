<?php

namespace App\Api\Modules\Export\Tests\UseCases;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\UseCases\GetExportUseCase;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class GetExportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnExportWhenFoundForUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $user->id]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($export, $user) {
                $mock->shouldReceive('findByIdForUser')
                    ->once()
                    ->with($export->id, $user->id)
                    ->andReturn($export);
            }),
        );

        // Act
        $useCase = app()->make(GetExportUseCase::class);
        $result = $useCase->execute($export->id, $user->id);

        // Assert
        $this->assertInstanceOf(Export::class, $result);
        $this->assertEquals($export->id, $result->id);
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenExportNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($nonExistentId, $user) {
                $mock->shouldReceive('findByIdForUser')
                    ->once()
                    ->with($nonExistentId, $user->id)
                    ->andReturn(null);
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Export não encontrado.');

        $useCase = app()->make(GetExportUseCase::class);
        $useCase->execute($nonExistentId, $user->id);
    }
}
