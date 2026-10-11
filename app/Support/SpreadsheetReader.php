<?php

namespace App\Support;

use DateTimeInterface;
use Generator;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Options as XlsxOptions;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Membaca baris dari berkas .xlsx atau .csv menjadi array string yang seragam.
 */
class SpreadsheetReader
{
    /**
     * @return Generator<int, list<string>> nomor baris (mulai 1) => nilai sel
     */
    public function rows(string $path, string $extension): Generator
    {
        // Baris kosong tetap dibaca agar nomor baris sama dengan yang terlihat di Excel.
        $reader = strtolower($extension) === 'xlsx'
            ? new XlsxReader(new XlsxOptions(SHOULD_PRESERVE_EMPTY_ROWS: true))
            : new CsvReader(new CsvOptions(SHOULD_PRESERVE_EMPTY_ROWS: true, FIELD_DELIMITER: $this->detectDelimiter($path)));

        $reader->open($path);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $index => $row) {
                    yield $index => array_map($this->normalize(...), $row->toArray());
                }

                break; // hanya sheet pertama yang berisi data
            }
        } finally {
            $reader->close();
        }
    }

    private function normalize(mixed $value): string
    {
        return match (true) {
            $value instanceof DateTimeInterface => $value->format('Y-m-d'),
            // Excel menyimpan NIS/NUPTK yang diketik sebagai angka dalam bentuk float.
            is_float($value) && floor($value) === $value => sprintf('%.0f', $value),
            is_bool($value) => $value ? '1' : '0',
            default => trim((string) $value),
        };
    }

    /** Excel berlokal Indonesia menyimpan CSV dengan titik koma. */
    private function detectDelimiter(string $path): string
    {
        $firstLine = (string) fgets($handle = fopen($path, 'r'));
        fclose($handle);

        return substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    }
}
