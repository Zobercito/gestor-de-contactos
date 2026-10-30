<?php

namespace App\Services\Csv;

class CsvDocument
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|null>>  $rows
     */
    public function __construct(
        public readonly array $headers,
        public readonly array $rows,
        public readonly string $delimiter,
        public readonly string $encoding,
        public readonly int $totalRows,
    ) {}

    /**
     * @return array<int, array<string, string|null>>
     */
    public function preview(int $limit = 12): array
    {
        return array_map(function (array $row) {
            $record = [];

            foreach ($this->headers as $index => $header) {
                $record[$header] = $row[$index] ?? null;
            }

            return $record;
        }, array_slice($this->rows, 0, $limit));
    }

    public function row(int $index): ?array
    {
        return $this->rows[$index] ?? null;
    }
}
