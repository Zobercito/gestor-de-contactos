<?php

namespace App\Services\Csv;

use App\Enums\ImportStatus;
use App\Jobs\ProcessContactImport;
use App\Models\Import;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactImportService
{
    private const MAX_STORED_ISSUES = 200;

    public function __construct(
        private CsvReader $reader,
        private HeaderMapper $mapper,
        private ContactRowNormalizer $normalizer,
    ) {}

    public function createImport(User $user, UploadedFile $file): Import
    {
        $disk = config('contacts.import.disk');
        $directory = config('contacts.import.directory');
        $path = $file->store($directory, $disk);

        try {
            $document = $this->reader->read(Storage::disk($disk)->path($path));
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw ValidationException::withMessages([
                'file' => $exception->getMessage(),
            ]);
        }

        if ($document->totalRows > config('contacts.import.max_rows')) {
            Storage::disk($disk)->delete($path);

            throw ValidationException::withMessages([
                'file' => 'El archivo supera el máximo de '.number_format(config('contacts.import.max_rows')).' filas.',
            ]);
        }

        $demoLimit = (int) config('demo.max_import_rows');

        if (config('demo.enabled') && $demoLimit > 0 && $document->totalRows > $demoLimit) {
            Storage::disk($disk)->delete($path);

            throw ValidationException::withMessages([
                'file' => 'En la demostración puedes importar hasta '.number_format($demoLimit).' filas por archivo.',
            ]);
        }

        return Import::create([
            'user_id' => $user->id,
            'filename' => basename($path),
            'original_name' => Str::limit($file->getClientOriginalName(), 180, ''),
            'path' => $path,
            'status' => ImportStatus::Pending,
            'delimiter' => $document->delimiter,
            'encoding' => $document->encoding,
            'total_rows' => $document->totalRows,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function inspect(Import $import): array
    {
        $document = $this->reader->read($this->absolutePath($import));
        $suggestions = $this->mapper->suggest($document->headers);
        $mapping = array_map(
            fn (?int $index) => $index === null ? null : $document->headers[$index],
            $suggestions,
        );

        return [
            'headers' => $document->headers,
            'preview' => $document->preview((int) config('contacts.import.preview_rows')),
            'delimiter' => $document->delimiter,
            'delimiter_label' => match ($document->delimiter) {
                ';' => 'punto y coma',
                "\t" => 'tabulador',
                '|' => 'barra vertical',
                default => 'coma',
            },
            'encoding' => $document->encoding,
            'total_rows' => $document->totalRows,
            'mapping' => $mapping,
            'fields' => HeaderMapper::fields(),
        ];
    }

    /**
     * @param  array<string, string|null>  $mapping
     * @return array{counts: array<string, int>, rows: array<int, array<string, mixed>>}
     */
    public function analyze(Import $import, array $mapping, int $previewLimit = 12): array
    {
        $document = $this->reader->read($this->absolutePath($import));
        $resolved = $this->resolveMapping($mapping, $document->headers);

        $detector = DuplicateDetector::fromDatabase();
        $counts = ['rows' => 0, 'ready' => 0, 'duplicates' => 0, 'errors' => 0, 'warnings' => 0];
        $preview = [];

        foreach ($document->rows as $index => $row) {
            $counts['rows']++;
            $evaluation = $this->evaluateRow($row, $resolved, $detector);

            match ($evaluation['status']) {
                'ready' => $counts['ready']++,
                'duplicate' => $counts['duplicates']++,
                default => $counts['errors']++,
            };

            if ($evaluation['warnings'] !== []) {
                $counts['warnings']++;
            }

            if (count($preview) < $previewLimit) {
                $preview[] = $evaluation + ['row' => $index + 2];
            }
        }

        return ['counts' => $counts, 'rows' => $preview];
    }

    public function queueOrProcess(Import $import): bool
    {
        if ($import->total_rows > config('contacts.import.queue_threshold')) {
            ProcessContactImport::dispatch($import);

            return true;
        }

        $this->process($import);

        return false;
    }

    public function process(Import $import): void
    {
        $import->update([
            'status' => ImportStatus::Processing,
            'started_at' => Carbon::now(),
        ]);

        try {
            $document = $this->reader->read($this->absolutePath($import));
            $resolved = $this->resolveMapping((array) $import->mapping, $document->headers);

            if ($resolved['name'] === null) {
                throw new \RuntimeException('El archivo necesita una columna de nombre emparejada.');
            }

            $detector = DuplicateDetector::fromDatabase();
            $actor = $import->user_id;

            $created = 0;
            $duplicates = 0;
            $errorCount = 0;
            $issues = [];
            $buffer = [];

            $flush = function () use (&$buffer, &$created, &$duplicates, &$errorCount, &$issues, $actor, $import) {
                $this->flushBatch($buffer, $import, $actor, $created, $duplicates, $errorCount, $issues);
            };

            foreach ($document->rows as $index => $row) {
                $rowNumber = $index + 2;

                if (count(array_filter($row, fn ($value) => $value !== null && $value !== '')) === 0) {
                    $errorCount++;
                    $this->addIssue($issues, $rowNumber, 'error', 'La fila está vacía.');

                    continue;
                }

                $entry = $this->normalizer->normalize($this->mapper->mapRecord($row, $resolved));

                if ($entry['errors'] !== []) {
                    $errorCount++;
                    $this->addIssue($issues, $rowNumber, 'error', implode(' ', $entry['errors']));

                    continue;
                }

                $duplicateReason = $detector->duplicateReason($entry['data']);

                if ($duplicateReason !== null) {
                    $duplicates++;
                    $this->addIssue($issues, $rowNumber, 'duplicate', $duplicateReason);

                    continue;
                }

                if ($entry['warnings'] !== []) {
                    $this->addIssue($issues, $rowNumber, 'warning', implode(' ', $entry['warnings']));
                }

                $detector->remember($entry['data']);

                $buffer[] = ['row' => $rowNumber, 'data' => $entry['data']];

                if (count($buffer) >= 200) {
                    $flush();
                }
            }

            $flush();

            $import->update([
                'status' => ImportStatus::Completed,
                'created_count' => $created,
                'duplicate_count' => $duplicates,
                'error_count' => $errorCount,
                'skipped_count' => $duplicates + $errorCount,
                'errors' => $issues,
                'finished_at' => Carbon::now(),
            ]);

            app(ActivityLogger::class)->log(
                'imported',
                $import,
                "Importó {$created} contactos desde {$import->original_name}",
                ['created' => $created, 'duplicates' => $duplicates, 'errors' => $errorCount],
            );
        } catch (Throwable $exception) {
            $import->update([
                'status' => ImportStatus::Failed,
                'errors' => [[
                    'row' => null,
                    'type' => 'error',
                    'message' => Str::limit($exception->getMessage(), 250),
                ]],
                'finished_at' => Carbon::now(),
            ]);

            throw $exception;
        }
    }

    /**
     * @param  array<int, array{row: int, data: array<string, mixed>}>  $buffer
     * @param  array<int, array<string, mixed>>  $issues
     */
    private function flushBatch(array &$buffer, Import $import, int $actor, int &$created, int &$duplicates, int &$errorCount, array &$issues): void
    {
        if ($buffer === []) {
            return;
        }

        $now = Carbon::now();
        $rows = [];

        foreach ($buffer as $item) {
            $rows[] = $item['data'] + [
                'created_by' => $actor,
                'updated_by' => $actor,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        try {
            DB::table('contacts')->insert($rows);
            $created += count($rows);
        } catch (Throwable) {
            foreach ($rows as $offset => $row) {
                try {
                    DB::table('contacts')->insert($row);
                    $created++;
                } catch (Throwable $exception) {
                    $errorCount++;
                    $this->addIssue(
                        $issues,
                        $buffer[$offset]['row'],
                        'error',
                        'No se pudo guardar: '.Str::limit($exception->getMessage(), 120),
                    );
                }
            }
        }

        $buffer = [];

        $import->update([
            'created_count' => $created,
            'duplicate_count' => $duplicates,
            'error_count' => $errorCount,
            'skipped_count' => $duplicates + $errorCount,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $issues
     */
    private function addIssue(array &$issues, int $rowNumber, string $type, string $message): void
    {
        if (count($issues) >= self::MAX_STORED_ISSUES) {
            return;
        }

        $issues[] = [
            'row' => $rowNumber,
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<string, int|null>  $mapping
     * @return array{status: string, messages: array<int, string>, warnings: array<int, string>, data: array<string, mixed>}
     */
    private function evaluateRow(array $row, array $mapping, DuplicateDetector $detector): array
    {
        if (count(array_filter($row, fn ($value) => $value !== null && $value !== '')) === 0) {
            return [
                'status' => 'error',
                'messages' => ['La fila está vacía.'],
                'warnings' => [],
                'data' => [],
            ];
        }

        $normalized = $this->normalizer->normalize($this->mapper->mapRecord($row, $mapping));

        if ($normalized['errors'] !== []) {
            return [
                'status' => 'error',
                'messages' => $normalized['errors'],
                'warnings' => $normalized['warnings'],
                'data' => $normalized['data'],
            ];
        }

        $duplicate = $detector->duplicateReason($normalized['data']);

        if ($duplicate !== null) {
            return [
                'status' => 'duplicate',
                'messages' => [$duplicate],
                'warnings' => $normalized['warnings'],
                'data' => $normalized['data'],
            ];
        }

        $detector->remember($normalized['data']);

        return [
            'status' => 'ready',
            'messages' => [],
            'warnings' => $normalized['warnings'],
            'data' => $normalized['data'],
        ];
    }

    /**
     * @param  array<string, string|null>  $mapping
     * @param  array<int, string>  $headers
     * @return array<string, int|null>
     */
    private function resolveMapping(array $mapping, array $headers): array
    {
        $resolved = [];
        $lookup = [];

        foreach ($headers as $index => $header) {
            $lookup[HeaderMapper::normalize($header)] ??= $index;
        }

        foreach (HeaderMapper::fields() as $field => $definition) {
            $header = $mapping[$field] ?? null;

            if (! is_string($header) || trim($header) === '') {
                $resolved[$field] = null;

                continue;
            }

            $resolved[$field] = $lookup[HeaderMapper::normalize($header)] ?? null;
        }

        return $resolved;
    }

    private function absolutePath(Import $import): string
    {
        return Storage::disk(config('contacts.import.disk'))->path($import->path);
    }
}
