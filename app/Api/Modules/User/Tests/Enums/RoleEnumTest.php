<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\Enums;

use App\Api\Modules\User\Enums\RoleEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class RoleEnumTest extends TestCase
{
    public static function labelProvider(): array
    {
        return [
            'admin_label' => [RoleEnum::Admin, 'Administrador'],
            'manager_label' => [RoleEnum::Manager, 'Gerente'],
            'user_label' => [RoleEnum::User, 'Usuário'],
        ];
    }

    #[DataProvider('labelProvider')]
    public function testLabelShouldReturnCorrectTranslationWhenCalled(
        RoleEnum $enum,
        string $expectedLabel,
    ): void {
        // Act
        $result = $enum->label();

        // Assert
        $this->assertEquals($expectedLabel, $result);
    }

    public function testValuesShouldReturnAllEnumValuesWhenCalled(): void
    {
        // Act
        $result = RoleEnum::values();

        // Assert
        $expected = ['admin', 'manager', 'user'];
        $this->assertEquals($expected, $result);
    }
}
