<?php

namespace App\Api\Modules\User\Repositories;

use App\Api\Modules\User\Data\UserQueryData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

class UserRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function getAllPaginated(UserQueryData $query): LengthAwarePaginator
    {
        return User::query()
            ->when($query->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($query->role, fn ($q, $role) => $q->where('role', $role))
            ->when($query->state, fn ($q, $state) => $q->where('state', $state))
            ->when($query->city, fn ($q, $city) => $q->where('city', $city))
            ->orderBy($query->sortBy ?? 'created_at', $query->sortOrder ?? 'desc')
            ->paginate(perPage: $query->perPage ?? UserQueryData::PER_PAGE_DEFAULT, page: $query->page ?? 1);
    }

    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LazyCollection<int, User>
     */
    public function getCursorForExport(array $filters): LazyCollection
    {
        return User::query()
            ->select(['id', 'name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'birth_date', 'role', 'created_at'])
            ->when(isset($filters['search']) && $filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['role']) && $filters['role'] !== '', fn ($q) => $q->where('role', $filters['role']))
            ->when(isset($filters['state']) && $filters['state'] !== '', fn ($q) => $q->where('state', $filters['state']))
            ->when(isset($filters['city']) && $filters['city'] !== '', fn ($q) => $q->where('city', $filters['city']))
            ->orderBy('id')
            ->cursor();
    }
}
