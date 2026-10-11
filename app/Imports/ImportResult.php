<?php

namespace App\Imports;

final class ImportResult
{
    public int $created = 0;
    public int $updated = 0;

    /** @var list<array{row: int, message: string}> */
    public array $failures = [];

    public function fail(int $row, string $message): void
    {
        $this->failures[] = ['row' => $row, 'message' => $message];
    }

    public function processed(): int
    {
        return $this->created + $this->updated;
    }

    /** Bentuk sederhana untuk disimpan di flash session. */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'failures' => $this->failures,
        ];
    }
}
