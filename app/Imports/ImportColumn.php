<?php

namespace App\Imports;

final readonly class ImportColumn
{
    /**
     * @param  array<int, mixed>  $rules  aturan validasi Laravel untuk nilai kolom ini
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $example,
        public string $note,
        public array $rules,
    ) {}
}
