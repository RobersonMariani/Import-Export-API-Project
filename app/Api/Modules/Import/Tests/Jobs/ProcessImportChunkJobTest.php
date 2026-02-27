<?php

namespace App\Api\Modules\Import\Tests\Jobs;

use App\Api\Modules\Import\Jobs\ProcessImportChunkJob;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class ProcessImportChunkJobTest extends TestCase
{

    public function testJobShouldHaveCorrectConfiguration(): void
    {
        // Arrange & Act
        $job = new ProcessImportChunkJob('import-uuid', [['name' => 'Test', 'email' => 'test@test.com', 'password' => 'pass']], 0);

        // Assert
        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 30, 60], $job->backoff);
        $this->assertEquals(300, $job->timeout);
    }

    public function testJobShouldBeBatchable(): void
    {
        // Assert
        $this->assertContains('Illuminate\Bus\Batchable', class_uses_recursive(ProcessImportChunkJob::class));
    }

    public function testJobShouldAcceptChunkData(): void
    {
        // Arrange
        $chunk = [
            ['name' => 'User 1', 'email' => 'user1@test.com', 'password' => 'pass1'],
            ['name' => 'User 2', 'email' => 'user2@test.com', 'password' => 'pass2'],
        ];

        // Act
        $job = new ProcessImportChunkJob('import-uuid', $chunk, 1);

        // Assert
        $this->assertEquals('import-uuid', $job->importId);
        $this->assertCount(2, $job->chunk);
        $this->assertEquals(1, $job->chunkIndex);
    }
}
