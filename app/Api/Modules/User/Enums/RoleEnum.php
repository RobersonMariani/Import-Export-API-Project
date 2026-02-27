<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Enums;

use App\Api\Support\Traits\EnumTrait;

enum RoleEnum: string
{
    use EnumTrait;

    case Admin = 'admin';
    case Manager = 'manager';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Manager => 'Gerente',
            self::User => 'Usuário',
        };
    }

    public function toDatabaseValue(): string
    {
        return $this->value;
    }
}
