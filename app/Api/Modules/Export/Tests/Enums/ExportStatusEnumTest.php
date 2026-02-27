<?php

namespace App\Api\Modules\Export\Tests\Enums;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class ExportStatusEnumTest extends TestCase
{
    public static function labelProvider(): array
    {
        return [
            'queued_label' => [ExportStatusEnum::Queued, 'Na fila'],
            'processing_label' => [ExportStatusEnum::Processing, 'Processando'],
            'completed_label' => [ExportStatusEnum::Completed, 'Concluído'],
            'failed_label' => [ExportStatusEnum::Failed, 'Falhou'],
        ];
    }

    #[DataProvider('labelProvider')]
    public function testLabelShouldReturnCorrectTranslationWhenCalled(
        ExportStatusEnum $enum,
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
        $result = ExportStatusEnum::values();

        // Assert
        $expected = ['queued', 'processing', 'completed', 'failed'];
        $this->assertEquals($expected, $result);
    }

    public function testIsFinalShouldReturnTrueForFinalStates(): void
    {
        $this->assertTrue(ExportStatusEnum::Completed->isFinal());
        $this->assertTrue(ExportStatusEnum::Failed->isFinal());
    }

    public function testIsFinalShouldReturnFalseForNonFinalStates(): void
    {
        $this->assertFalse(ExportStatusEnum::Queued->isFinal());
        $this->assertFalse(ExportStatusEnum::Processing->isFinal());
    }
}
