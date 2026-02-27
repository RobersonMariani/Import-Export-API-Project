<?php

namespace App\Api\Modules\Export\Services;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use League\Csv\Writer;

class ExportService
{
    private const EXPORTS_DIR = 'exports';

    private const EXPIRES_MINUTES = 60;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ExportRepository $exportRepository,
    ) {}

    public function processExport(Export $export): void
    {
        $filters = $export->filters ?? [];
        $cursor = $this->userRepository->getCursorForExport($filters);

        $extension = $export->compressed ? 'csv.gz' : 'csv';
        $relativePath = self::EXPORTS_DIR.'/'.$export->id.'.'.$extension;
        $fullPath = Storage::path($relativePath);

        $this->ensureExportsDirectoryExists();

        $totalRecords = $export->compressed
            ? $this->writeCsvToGzip($cursor, $fullPath)
            : $this->writeCsv($cursor, $fullPath);

        $this->exportRepository->update($export, [
            'file_path' => $relativePath,
            'total_records' => $totalRecords,
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
        ]);
    }

    public function getTemporaryDownloadUrl(Export $export): string
    {
        if ($export->file_path === null) {
            throw new \RuntimeException('Export ainda não possui arquivo disponível');
        }

        return Storage::disk('local')->temporaryUrl(
            $export->file_path,
            now()->addMinutes(15)
        );
    }

    /** @return list<string> */
    private function getCsvHeaders(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'address',
            'city',
            'state',
            'zip_code',
            'birth_date',
            'role',
            'created_at',
        ];
    }

    /** @return array<int, string|null> */
    private function userToCsvRow(User $user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->address,
            $user->city,
            $user->state,
            $user->zip_code,
            $user->birth_date?->format('Y-m-d'),
            $user->role,
            $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  LazyCollection<int, User>  $cursor
     */
    private function writeCsv(LazyCollection $cursor, string $fullPath): int
    {
        $writer = Writer::createFromPath($fullPath, 'w+');
        $writer->insertOne($this->getCsvHeaders());

        $count = 0;
        foreach ($cursor as $user) {
            $writer->insertOne($this->userToCsvRow($user));
            $count++;
        }

        return $count;
    }

    /**
     * @param  LazyCollection<int, User>  $cursor
     */
    private function writeCsvToGzip(LazyCollection $cursor, string $fullPath): int
    {
        $tempCsvPath = $fullPath.'.tmp.csv';
        $count = $this->writeCsv($cursor, $tempCsvPath);

        $stream = gzopen($fullPath, 'w9');
        if ($stream === false) {
            unlink($tempCsvPath);

            throw new \RuntimeException('Não foi possível criar arquivo gzip');
        }

        $handle = fopen($tempCsvPath, 'r');
        if ($handle === false) {
            gzclose($stream);
            unlink($tempCsvPath);

            throw new \RuntimeException('Não foi possível ler arquivo CSV temporário');
        }

        while (! feof($handle)) {
            $chunk = fread($handle, 8192);
            if ($chunk !== false) {
                gzwrite($stream, $chunk);
            }
        }

        fclose($handle);
        gzclose($stream);
        unlink($tempCsvPath);

        return $count;
    }

    private function ensureExportsDirectoryExists(): void
    {
        $path = Storage::path(self::EXPORTS_DIR);
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
