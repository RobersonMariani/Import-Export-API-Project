<?php

namespace App\Api\Support\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model): void {
            static::recordAudit($model, 'created', [], $model->getAttributes());
        });

        static::updated(function ($model): void {
            $original = $model->getOriginal();
            $changes = $model->getChanges();

            $oldValues = [];
            $newValues = [];
            foreach ($changes as $key => $value) {
                if ($key === 'updated_at') {
                    continue;
                }
                $oldValues[$key] = $original[$key] ?? null;
                $newValues[$key] = $value;
            }

            if (! empty($newValues)) {
                static::recordAudit($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model): void {
            static::recordAudit($model, 'deleted', $model->getAttributes(), []);
        });
    }

    /** @param array<string, mixed> $oldValues @param array<string, mixed> $newValues */
    private static function recordAudit(mixed $model, string $event, array $oldValues, array $newValues): void
    {
        $userId = null;
        $ipAddress = null;
        $userAgent = null;
        $correlationId = null;

        if (app()->bound('request')) {
            $request = request();
            $userId = $request->user()?->getKey();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
        }

        if (app()->bound('correlation_id')) {
            $correlationId = app('correlation_id');
        }

        AuditLog::query()->create([
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'user_id' => $userId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'correlation_id' => $correlationId,
        ]);
    }
}
