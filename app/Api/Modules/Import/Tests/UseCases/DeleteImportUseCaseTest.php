<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\UseCases;

use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\UseCases\DeleteImportUseCase;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class DeleteImportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldDeleteImportWhenFoundForUser(): void
    {
        // Arrange
        Storage::fake('local');
        $user = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $user->id, 'file_path' => 'imports/test.csv']);
        Storage::disk('local')->put('imports/test.csv', 'test');

        // Act
        $useCase = app()->make(DeleteImportUseCase::class);
        $useCase->execute($import->id, $user->id);

        // Assert
        $this->assertDatabaseMissing('imports', ['id' => $import->id]);
        Storage::disk('local')->assertMissing('imports/test.csv');
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenImportNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(DeleteImportUseCase::class);
        $useCase->execute($nonExistentId, $user->id);
    }
}
