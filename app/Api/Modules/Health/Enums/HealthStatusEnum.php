<?php

namespace App\Api\Modules\Health\Enums;

use App\Api\Support\Traits\EnumTrait;

enum HealthStatusEnum: string
{
    use EnumTrait;

    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unhealthy = 'unhealthy';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Saudável',
            self::Degraded => 'Degradado',
            self::Unhealthy => 'Indisponível',
        };
    }

    public function toDatabaseValue(): string
    {
        return $this->value;
    }
}
