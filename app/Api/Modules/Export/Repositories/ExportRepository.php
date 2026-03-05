<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Repositories;

use App\Api\Modules\Export\Data\ExportQueryData;
use App\Models\Export;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExportRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Export
    {
        return Export::query()->create($data);
    }

    public function findById(string $id): ?Export
    {
        return Export::query()->find($id);
    }

    public function getAllPaginated(ExportQueryData $query, ?int $userId = null): LengthAwarePaginator
    {
        return Export::query()
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->when($query->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: $query->perPage ?? ExportQueryData::PER_PAGE_DEFAULT, page: $query->page ?? 1);
    }

    public function findByIdForUser(string $id, int $userId): ?Export
    {
        return Export::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /** @param array<string, mixed> $data */
    public function update(Export $export, array $data): Export
    {
        $export->update($data);

        return $export->refresh();
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        return Export::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();
    }

    public function updateStatus(Export $export, string $status, ?DateTimeInterface $finishedAt = null): Export
    {
        $data = ['status' => $status];

        if ($finishedAt !== null) {
            $data['finished_at'] = $finishedAt;
        }

        return $this->update($export, $data);
    }
}
