<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Jobs;

use App\Api\Modules\Import\Events\ChunkProcessedEvent;
use App\Api\Modules\Import\Repositories\ImportRepository;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Throwable;

class ProcessImportChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    /**
     * @param array<int, array<string, string>> $chunk
     */
    public function __construct(
        public string $importId,
        public array $chunk,
        public int $chunkIndex = 0,
    ) {
        $this->onQueue('imports');
    }

    public function handle(ImportRepository $importRepository): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $import = $importRepository->findById($this->importId);

        if ($import === null) {
            return;
        }

        $successCount = 0;
        $failureCount = 0;
        $failures = [];
        $upsertRecords = [];
        $chunkSize = 1000;
        $baseLineNumber = ($this->chunkIndex * $chunkSize) + 1;
        $now = now()->toDateTimeString();

        $updateColumns = ['name', 'phone', 'address', 'city', 'state', 'zip_code', 'birth_date', 'role', 'updated_at'];

        foreach ($this->chunk as $index => $row) {
            $lineNumber = $baseLineNumber + $index;

            try {
                $email = trim($row['email'] ?? '');

                if ($email === '') {
                    throw new InvalidArgumentException('Email é obrigatório');
                }

                $name = trim($row['name'] ?? '');

                if ($name === '') {
                    throw new InvalidArgumentException('Name é obrigatório');
                }

                $password = trim($row['password'] ?? '');

                if ($password === '') {
                    throw new InvalidArgumentException('Password é obrigatório');
                }

                $upsertRecords[] = [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]),
                    'phone' => $row['phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? null,
                    'zip_code' => $row['zip_code'] ?? null,
                    'birth_date' => ! empty($row['birth_date']) ? $row['birth_date'] : null,
                    'role' => $row['role'] ?? 'user',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $successCount++;
            } catch (Throwable $e) {
                $failureCount++;
                $failures[] = [
                    'import_id' => $this->importId,
                    'line_number' => $lineNumber,
                    'payload' => json_encode($row),
                    'error_message' => $e->getMessage(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($importRepository, $upsertRecords, $failures, $updateColumns): void {
            if (! empty($upsertRecords)) {
                $importRepository->bulkUpsertUsers($upsertRecords, ['email'], $updateColumns);
            }

            if (! empty($failures)) {
                $importRepository->bulkInsertFailures($failures);
            }
        });

        $importRepository->incrementProgress($this->importId, $successCount, $failureCount);

        Event::dispatch(new ChunkProcessedEvent(
            $import->refresh(),
            $successCount,
            $failureCount,
        ));
    }
}
