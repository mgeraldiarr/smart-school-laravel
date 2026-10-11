<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Services\TeacherService;
use App\Support\SpreadsheetReader;

class TeacherImport extends SpreadsheetImport
{
    public function __construct(SpreadsheetReader $reader, private TeacherService $teachers)
    {
        parent::__construct($reader);
    }

    public function subject(): string
    {
        return 'guru';
    }

    public function columns(): array
    {
        return [
            new ImportColumn('nuptk', 'NUPTK', '1234567890123456', 'Hanya angka. Dipakai guru untuk masuk ke aplikasi.', ['required', 'regex:/^\d+$/', 'max:25']),
            new ImportColumn('full_name', 'Nama guru', 'Dewi Lestari, S.Pd', 'Nama lengkap beserta gelar.', ['required', 'string', 'max:255']),
            new ImportColumn('email', 'Email', 'dewi.lestari@gmail.com', 'Untuk kode OTP saat guru lupa kata sandi.', ['required', 'email', 'max:255']),
            new ImportColumn('phone_number', 'Nomor HP', '081234567890', 'Boleh dikosongkan.', ['nullable', 'regex:/^[0-9+\-\s]+$/', 'max:20']),
        ];
    }

    public function exampleRows(): array
    {
        return [
            ['1234567890123456', 'Dewi Lestari, S.Pd', 'dewi.lestari@gmail.com', '081234567890'],
            ['6543210987654321', 'Agus Salim, M.Pd', 'agus.salim@gmail.com', ''],
        ];
    }

    protected function messages(): array
    {
        return [
            'nuptk.regex' => 'NUPTK ":input" hanya boleh berisi angka.',
            'phone_number.regex' => 'Nomor HP hanya boleh berisi angka, spasi, +, atau -.',
        ];
    }

    protected function prepare(array $data): array
    {
        $data['email'] = strtolower($data['email']);

        return $data;
    }

    protected function persist(array $data): bool
    {
        $isNew = ! Teacher::where('nuptk', $data['nuptk'])->exists();

        $this->teachers->save($data);

        return $isNew;
    }
}
