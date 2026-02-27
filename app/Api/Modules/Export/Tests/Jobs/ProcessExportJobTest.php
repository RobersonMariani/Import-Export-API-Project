<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Jobs;

use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class ProcessExportJobTest extends TestCase
{
    use RefreshDatabase;

    public function testJobShouldBeDispatchedToExportsQueue(): void
    {
        // Arrange
        Bus::fake();
        $user = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $user->id]);

        // Act
        ProcessExportJob::dispatch($export->id)->onQueue('exports');

        // Assert
        Bus::assertDispatched(ProcessExportJob::class, function (ProcessExportJob $job) use ($export) {
            return $job->exportId === $export->id && $job->queue === 'exports';
        });
    }

    public function testJobShouldHaveCorrectPayload(): void
    {
        // Arrange
        $exportId = '550e8400-e29b-41d4-a716-446655440000';

        // Act
        $job = new ProcessExportJob($exportId);

        // Assert
        $this->assertEquals($exportId, $job->exportId);
    }

    public function testJobShouldHaveCorrectTriesAndTimeout(): void
    {
        // Arrange
        $job = new ProcessExportJob('550e8400-e29b-41d4-a716-446655440000');

        // Assert
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(600, $job->timeout);
    }
}
