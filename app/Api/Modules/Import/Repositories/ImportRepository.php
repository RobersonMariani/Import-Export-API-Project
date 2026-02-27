<?php

namespace App\Api\Modules\Import\Repositories;

use App\Api\Modules\Import\Data\ImportQueryData;
use App\Models\Import;
use App\Models\ImportFailure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ImportRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Import
    {
        return Import::query()->create($data);
    }

    public function findById(string $id): ?Import
    {
        return Import::query()->find($id);
    }

    public function findByIdForUser(string $id, int $userId): ?Import
    {
        return Import::query()->where('id', $id)->where('user_id', $userId)->first();
    }

    public function getAllPaginated(ImportQueryData $query, ?int $userId = null): LengthAwarePaginator
    {
        return Import::query()
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->when($query->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: $query->perPage ?? ImportQueryData::PER_PAGE_DEFAULT, page: $query->page ?? 1);
    }

    /** @param array<string, mixed> $data */
    public function update(Import $import, array $data): Import
    {
        $import->update($data);

        return $import->refresh();
    }

    public function incrementProgress(string $importId, int $successCount, int $failureCount): void
    {
        DB::table('imports')
            ->where('id', $importId)
            ->update([
                'success_count' => DB::raw("success_count + {$successCount}"),
                'failure_count' => DB::raw("failure_count + {$failureCount}"),
                'progress' => DB::raw('progress + '.($successCount + $failureCount)),
                'updated_at' => now(),
            ]);
    }

    public function updateStatus(Import $import, string $status, ?\DateTimeInterface $finishedAt = null): Import
    {
        $data = ['status' => $status];
        if ($finishedAt !== null) {
            $data['finished_at'] = $finishedAt;
        }

        return $this->update($import, $data);
    }

    /**
     * @param  array<int, array<string, mixed>>  $failures
     */
    public function bulkInsertFailures(array $failures): void
    {
        if (empty($failures)) {
            return;
        }

        ImportFailure::query()->insert($failures);
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        return Import::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @param  list<string>  $uniqueBy
     * @param  list<string>  $updateColumns
     */
    public function bulkUpsertUsers(array $records, array $uniqueBy = ['email'], array $updateColumns = []): int
    {
        if (empty($records)) {
            return 0;
        }

        return DB::table('users')->upsert($records, $uniqueBy, $updateColumns);
    }
}
