<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Services;

use Generator;
use InvalidArgumentException;
use League\Csv\Reader;

class CsvParserService
{
    /** @var list<string> */
    public const REQUIRED_HEADERS = ['name', 'email', 'password'];

    /** @var list<string> */
    public const OPTIONAL_HEADERS = ['phone', 'address', 'city', 'state', 'zip_code', 'birth_date', 'role'];

    /**
     * @return Generator<int, array<int, array<string, string>>>
     */
    public function readChunks(string $filePath, int $chunkSize = 1000): Generator
    {
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        $this->validateHeaders($reader->getHeader());

        $chunk = [];
        $chunkIndex = 0;

        foreach ($reader->getRecords() as $record) {
            $chunk[] = $this->sanitizeRow($record);

            if (count($chunk) >= $chunkSize) {
                yield $chunkIndex => $chunk;
                $chunk = [];
                $chunkIndex++;
            }
        }

        if (! empty($chunk)) {
            yield $chunkIndex => $chunk;
        }
    }

    public function countRecords(string $filePath): int
    {
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        $this->validateHeaders($reader->getHeader());

        $count = 0;

        foreach ($reader->getRecords() as $_) {
            $count++;
        }

        return $count;
    }

    /**
     * @return array{total: int, chunks: list<list<array<string, string>>>}
     */
    public function readAllChunks(string $filePath, int $chunkSize = 1000): array
    {
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        $this->validateHeaders($reader->getHeader());

        $chunks = [];
        $chunk = [];
        $total = 0;

        foreach ($reader->getRecords() as $record) {
            $chunk[] = $this->sanitizeRow($record);
            $total++;

            if (count($chunk) >= $chunkSize) {
                $chunks[] = $chunk;
                $chunk = [];
            }
        }

        if (! empty($chunk)) {
            $chunks[] = $chunk;
        }

        return ['total' => $total, 'chunks' => $chunks];
    }

    /**
     * @param array<string, string> $row
     *
     * @return array<string, string>
     */
    public function sanitizeRow(array $row): array
    {
        $result = [];

        foreach ($row as $key => $value) {
            $result[$this->normalizeHeader($key)] = $this->sanitizeCell((string) $value);
        }

        return $result;
    }

    public function sanitizeCell(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return $trimmed;
        }

        $firstChar = $trimmed[0];

        if (in_array($firstChar, ['=', '+', '-', '@'], true)) {
            return "'".$trimmed;
        }

        return $trimmed;
    }

    /** @param list<string> $headers */
    public function validateHeaders(array $headers): void
    {
        $normalized = array_map(fn (string $h) => $this->normalizeHeader($h), $headers);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (! in_array($required, $normalized, true)) {
                throw new InvalidArgumentException("Cabeçalho obrigatório ausente: {$required}");
            }
        }
    }

    public function normalizeHeader(string $header): string
    {
        return strtolower(trim(str_replace(' ', '_', $header)));
    }
}
