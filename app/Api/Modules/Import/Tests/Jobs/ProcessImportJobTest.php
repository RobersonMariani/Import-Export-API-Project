<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Jobs;

use App\Api\Modules\Import\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class ProcessImportJobTest extends TestCase
{
    use RefreshDatabase;

    public function testJobShouldBeDispatchedToImportsQueue(): void
    {
        // Arrange
        Bus::fake();
        $user = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $user->id]);

        // Act
        ProcessImportJob::dispatch($import->id)->onQueue('imports');

        // Assert
        Bus::assertDispatched(ProcessImportJob::class, function (ProcessImportJob $job) use ($import) {
            return $job->importId === $import->id && $job->queue === 'imports';
        });
    }

    public function testJobShouldHaveCorrectPayload(): void
    {
        // Arrange
        $importId = '550e8400-e29b-41d4-a716-446655440000';

        // Act
        $job = new ProcessImportJob($importId);

        // Assert
        $this->assertEquals($importId, $job->importId);
    }
}
