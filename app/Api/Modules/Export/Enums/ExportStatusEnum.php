<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Enums;

use App\Api\Support\Traits\EnumTrait;

enum ExportStatusEnum: string
{
    use EnumTrait;

    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Na fila',
            self::Processing => 'Processando',
            self::Completed => 'Concluído',
            self::Failed => 'Falhou',
        };
    }

    public function toDatabaseValue(): string
    {
        return $this->value;
    }

    public function isFinal(): bool
    {
        return $this->has([self::Completed, self::Failed]);
    }
}
