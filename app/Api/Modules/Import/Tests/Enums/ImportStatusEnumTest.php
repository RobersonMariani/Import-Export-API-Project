<?php

namespace App\Api\Modules\Import\Tests\Enums;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class ImportStatusEnumTest extends TestCase
{
    public static function labelProvider(): array
    {
        return [
            'queued_label' => [ImportStatusEnum::Queued, 'Na fila'],
            'processing_label' => [ImportStatusEnum::Processing, 'Processando'],
            'partial_label' => [ImportStatusEnum::Partial, 'Parcial'],
            'completed_label' => [ImportStatusEnum::Completed, 'Concluído'],
            'failed_label' => [ImportStatusEnum::Failed, 'Falhou'],
        ];
    }

    #[DataProvider('labelProvider')]
    public function testLabelShouldReturnCorrectTranslationWhenCalled(
        ImportStatusEnum $enum,
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
        $result = ImportStatusEnum::values();

        // Assert
        $expected = ['queued', 'processing', 'partial', 'completed', 'failed'];
        $this->assertEquals($expected, $result);
    }

    public function testIsFinalShouldReturnTrueForFinalStates(): void
    {
        $this->assertTrue(ImportStatusEnum::Partial->isFinal());
        $this->assertTrue(ImportStatusEnum::Completed->isFinal());
        $this->assertTrue(ImportStatusEnum::Failed->isFinal());
    }

    public function testIsFinalShouldReturnFalseForNonFinalStates(): void
    {
        $this->assertFalse(ImportStatusEnum::Queued->isFinal());
        $this->assertFalse(ImportStatusEnum::Processing->isFinal());
    }
}
