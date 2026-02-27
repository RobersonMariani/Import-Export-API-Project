<?php

namespace App\Api\Modules\Health\Tests\Enums;

use App\Api\Modules\Health\Enums\HealthStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('health')]
class HealthStatusEnumTest extends TestCase
{
    public static function labelProvider(): array
    {
        return [
            'healthy_label' => [HealthStatusEnum::Healthy, 'Saudável'],
            'degraded_label' => [HealthStatusEnum::Degraded, 'Degradado'],
            'unhealthy_label' => [HealthStatusEnum::Unhealthy, 'Indisponível'],
        ];
    }

    #[DataProvider('labelProvider')]
    public function testLabelShouldReturnCorrectTranslationWhenCalled(
        HealthStatusEnum $enum,
        string $expectedLabel
    ): void {
        // Act
        $result = $enum->label();

        // Assert
        $this->assertEquals($expectedLabel, $result);
    }

    public function testValuesShouldReturnAllEnumValuesWhenCalled(): void
    {
        // Act
        $result = HealthStatusEnum::values();

        // Assert
        $expected = ['healthy', 'degraded', 'unhealthy'];
        $this->assertEquals($expected, $result);
    }
}
