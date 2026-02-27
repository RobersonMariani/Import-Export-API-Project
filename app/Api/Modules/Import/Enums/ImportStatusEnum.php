<?php

namespace App\Api\Modules\Import\Enums;

use App\Api\Support\Traits\EnumTrait;

enum ImportStatusEnum: string
{
    use EnumTrait;

    case Queued = 'queued';
    case Processing = 'processing';
    case Partial = 'partial';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Na fila',
            self::Processing => 'Processando',
            self::Partial => 'Parcial',
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
        return $this->has([self::Partial, self::Completed, self::Failed]);
    }
}
