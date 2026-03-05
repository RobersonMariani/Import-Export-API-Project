<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\UseCases;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Api\Modules\Export\UseCases\RetryExportUseCase;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class RetryExportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldResetAndRedispatchWhenExportFailed(): void
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => ExportStatusEnum::Failed->value,
            'error_message' => 'Some error',
            'total_records' => 100,
        ]);

        // Act
        $useCase = app()->make(RetryExportUseCase::class);
        $result = $useCase->execute($export->id, $user->id);

        // Assert
        $this->assertEquals(ExportStatusEnum::Queued->value, $result->status);
        $this->assertEquals(0, $result->total_records);
        $this->assertNull($result->error_message);
        Queue::assertPushed(ProcessExportJob::class);
    }

    public function testExecuteShouldThrowRuntimeExceptionWhenExportNotFailed(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => ExportStatusEnum::Completed->value,
        ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);

        $useCase = app()->make(RetryExportUseCase::class);
        $useCase->execute($export->id, $user->id);
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenExportNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(RetryExportUseCase::class);
        $useCase->execute('00000000-0000-0000-0000-000000000000', $user->id);
    }
}
