<?php

namespace App\Services\Csv;

use League\Csv\Reader;
use RuntimeException;

class CsvReader
{
    private const DELIMITERS = [',', ';', "\t", '|'];

    public function read(string $path): CsvDocument
    {
        if (! is_file($path)) {
            throw new RuntimeException('El archivo CSV no existe.');
        }

        [$content, $encoding] = $this->toUtf8((string) file_get_contents($path));
        $delimiter = $this->detectDelimiter($content);

        $reader = Reader::fromString($content);
        $reader->setDelimiter($delimiter);

        $rows = [];

        foreach ($reader->getRecords() as $record) {
            $rows[] = array_map(
                fn ($value) => $value === null ? null : trim((string) $value),
                $record,
            );
        }

        if ($rows === []) {
            throw new RuntimeException('El archivo está vacío.');
        }

        $headers = array_map(fn ($header) => $this->cleanHeader((string) $header), array_shift($rows));

        if (count(array_filter($headers)) === 0) {
            throw new RuntimeException('No se encontraron encabezados en el archivo.');
        }

        $width = max(array_map('count', $rows) ?: [count($headers)]);
        $normalizedRows = array_map(function (array $row) use ($width) {
            return array_pad(array_slice($row, 0, $width), $width, null);
        }, $rows);

        $headers = array_pad(array_slice($headers, 0, $width), $width, 'Columna '.($width + 1));

        return new CsvDocument(
            headers: $headers,
            rows: $normalizedRows,
            delimiter: $delimiter,
            encoding: $encoding,
            totalRows: count($normalizedRows),
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function toUtf8(string $content): array
    {
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            return [substr($content, 3), 'UTF-8'];
        }

        if (mb_check_encoding($content, 'UTF-8')) {
            return [$content, 'UTF-8'];
        }

        $encoding = mb_detect_encoding($content, ['Windows-1252', 'ISO-8859-1', 'UTF-8'], true) ?: 'ISO-8859-1';

        return [mb_convert_encoding($content, 'UTF-8', $encoding), $encoding];
    }

    private function detectDelimiter(string $content): string
    {
        $lines = array_values(array_filter(
            preg_split('/\r\n|\n|\r/', $content) ?: [],
            fn (string $line) => trim($line) !== '',
        ));

        $lines = array_slice($lines, 0, 8);

        if ($lines === []) {
            return ',';
        }

        $best = ',';
        $bestScore = 0;

        foreach (self::DELIMITERS as $delimiter) {
            $counts = array_map(fn (string $line) => count(str_getcsv($line, $delimiter, '"', '')), $lines);
            $max = max($counts);

            if ($max < 2) {
                continue;
            }

            $consistent = count(array_filter($counts, fn (int $count) => $count === $max)) / count($counts);
            $score = $max * $consistent;

            if ($score > $bestScore) {
                $best = $delimiter;
                $bestScore = $score;
            }
        }

        return $best;
    }

    private function cleanHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = trim($header);
        $header = trim($header, "\"' \t\n\r\0\x0B");

        return preg_replace('/\s+/', ' ', $header) ?: 'Columna';
    }
}
