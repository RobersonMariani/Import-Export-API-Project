<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\UseCases;

use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\UseCases\GetImportUseCase;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class GetImportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnImportWhenFoundAndUserMatches(): void
    {
        // Arrange
        $user = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $user->id]);

        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) use ($import) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with($import->id)
                    ->andReturn($import);
            }),
        );

        // Act
        $useCase = app()->make(GetImportUseCase::class);
        $result = $useCase->execute($import->id, $user->id);

        // Assert
        $this->assertInstanceOf(Import::class, $result);
        $this->assertEquals($import->id, $result->id);
    }

    public function testExecuteShouldReturnImportWhenUserIdIsNull(): void
    {
        // Arrange
        $import = Import::factory()->create();

        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) use ($import) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with($import->id)
                    ->andReturn($import);
            }),
        );

        // Act
        $useCase = app()->make(GetImportUseCase::class);
        $result = $useCase->execute($import->id, null);

        // Assert
        $this->assertInstanceOf(Import::class, $result);
        $this->assertEquals($import->id, $result->id);
    }

    public function testExecuteShouldThrowWhenImportNotFound(): void
    {
        // Arrange
        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with('non-existent-uuid')
                    ->andReturn(null);
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Import não encontrado.');

        $useCase = app()->make(GetImportUseCase::class);
        $useCase->execute('non-existent-uuid', 1);
    }

    public function testExecuteShouldThrowWhenUserDoesNotOwnImport(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $owner->id]);

        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) use ($import) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with($import->id)
                    ->andReturn($import);
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Import não encontrado.');

        $useCase = app()->make(GetImportUseCase::class);
        $useCase->execute($import->id, $otherUser->id);
    }
}
