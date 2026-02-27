<?php

namespace App\Api\Modules\Export\Repositories;

use App\Models\Export;

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

    public function updateStatus(Export $export, string $status, ?\DateTimeInterface $finishedAt = null): Export
    {
        $data = ['status' => $status];
        if ($finishedAt !== null) {
            $data['finished_at'] = $finishedAt;
        }

        return $this->update($export, $data);
    }
}
