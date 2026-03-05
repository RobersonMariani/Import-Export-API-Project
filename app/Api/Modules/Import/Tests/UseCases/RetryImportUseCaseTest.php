<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\UseCases;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Jobs\ProcessImportJob;
use App\Api\Modules\Import\UseCases\RetryImportUseCase;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class RetryImportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldResetAndRedispatchWhenImportFailed(): void
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create();
        $import = Import::factory()->create([
            'user_id' => $user->id,
            'status' => ImportStatusEnum::Failed->value,
            'error_message' => 'Some error',
            'progress' => 50,
            'success_count' => 30,
            'failure_count' => 20,
        ]);

        // Act
        $useCase = app()->make(RetryImportUseCase::class);
        $result = $useCase->execute($import->id, $user->id);

        // Assert
        $this->assertEquals(ImportStatusEnum::Queued->value, $result->status);
        $this->assertEquals(0, $result->progress);
        $this->assertEquals(0, $result->success_count);
        $this->assertEquals(0, $result->failure_count);
        $this->assertNull($result->error_message);
        Queue::assertPushed(ProcessImportJob::class);
    }

    public function testExecuteShouldThrowRuntimeExceptionWhenImportNotFailed(): void
    {
        // Arrange
        $user = User::factory()->create();
        $import = Import::factory()->create([
            'user_id' => $user->id,
            'status' => ImportStatusEnum::Completed->value,
        ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);

        $useCase = app()->make(RetryImportUseCase::class);
        $useCase->execute($import->id, $user->id);
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenImportNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(RetryImportUseCase::class);
        $useCase->execute('00000000-0000-0000-0000-000000000000', $user->id);
    }
}
