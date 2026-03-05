<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\UseCases;

use App\Api\Modules\Export\UseCases\DeleteExportUseCase;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class DeleteExportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldDeleteExportWhenFoundForUser(): void
    {
        // Arrange
        Storage::fake('local');
        $user = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $user->id, 'file_path' => 'exports/test.csv']);
        Storage::disk('local')->put('exports/test.csv', 'test');

        // Act
        $useCase = app()->make(DeleteExportUseCase::class);
        $useCase->execute($export->id, $user->id);

        // Assert
        $this->assertDatabaseMissing('exports', ['id' => $export->id]);
        Storage::disk('local')->assertMissing('exports/test.csv');
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenExportNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(DeleteExportUseCase::class);
        $useCase->execute($nonExistentId, $user->id);
    }
}
