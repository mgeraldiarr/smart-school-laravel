<?php

namespace App\Imports;

use App\Exceptions\DataConflict;
use App\Support\SpreadsheetReader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Dasar impor berbasis spreadsheet: baris pertama adalah judul kolom,
 * kolom dibaca berdasarkan urutan, dan setiap baris divalidasi sendiri-sendiri
 * sehingga satu baris yang salah tidak membatalkan baris lainnya.
 */
abstract class SpreadsheetImport
{
    public const DEFAULT_PASSWORD = 'password123';

    public function __construct(protected SpreadsheetReader $reader) {}

    /** Nama data yang diimpor, misal "siswa". */
    abstract public function subject(): string;

    /** @return list<ImportColumn> */
    abstract public function columns(): array;

    /**
     * Simpan satu baris yang sudah valid.
     *
     * @param  array<string, string>  $data
     * @return bool true jika data baru dibuat, false jika memperbarui data lama
     *
     * @throws DataConflict jika baris bertentangan dengan data yang sudah ada
     */
    abstract protected function persist(array $data): bool;

    /** @return list<array<int, string>> baris contoh untuk template */
    public function exampleRows(): array
    {
        return [array_map(fn (ImportColumn $column) => $column->example, $this->columns())];
    }

    public function import(string $path, string $extension): ImportResult
    {
        $result = new ImportResult();
        $columns = $this->columns();

        foreach ($this->reader->rows($path, $extension) as $rowNumber => $values) {
            if ($rowNumber === 1 || $this->isBlank($values)) {
                continue;
            }

            $data = [];
            foreach ($columns as $i => $column) {
                $data[$column->key] = $values[$i] ?? '';
            }
            $data = $this->prepare($data);

            $validator = Validator::make(
                $data,
                collect($columns)->mapWithKeys(fn ($c) => [$c->key => $c->rules])->all(),
                $this->messages(),
                collect($columns)->mapWithKeys(fn ($c) => [$c->key => $c->label])->all(),
            );

            if ($validator->fails()) {
                $result->fail($rowNumber, $validator->errors()->first());
                continue;
            }

            try {
                $created = DB::transaction(fn () => $this->persist($data));
                $created ? $result->created++ : $result->updated++;
            } catch (DataConflict $e) {
                $result->fail($rowNumber, $e->getMessage());
            } catch (Throwable $e) {
                report($e);
                $result->fail($rowNumber, 'Baris ini gagal disimpan karena kesalahan sistem.');
            }
        }

        return $result;
    }

    /** @return array<string, string> pesan validasi khusus, format sama dengan Validator */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Rapikan nilai sebelum divalidasi.
     *
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    protected function prepare(array $data): array
    {
        return $data;
    }

    /**
     * Seragamkan tanggal menjadi "2011-05-12". Menerima format Indonesia
     * ("12/05/2011", "12-05-2011") dan nomor seri tanggal Excel ("40675"),
     * yang muncul bila sel tanggal tidak berformat tanggal.
     */
    protected function normalizeDate(string $value): string
    {
        if (preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4})$#', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        if (preg_match('/^\d{4,5}$/', $value) && (int) $value > 3000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        return $value;
    }

    private function isBlank(array $values): bool
    {
        return implode('', $values) === '';
    }
}
