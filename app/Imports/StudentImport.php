<?php

namespace App\Imports;

use App\Models\Student;
use App\Services\StudentService;
use App\Support\SpreadsheetReader;

class StudentImport extends SpreadsheetImport
{
    public function __construct(SpreadsheetReader $reader, private StudentService $students)
    {
        parent::__construct($reader);
    }

    public function subject(): string
    {
        return 'siswa';
    }

    public function columns(): array
    {
        return [
            new ImportColumn('nis', 'NIS', '20241010', 'Unik per siswa. Orang tua masuk ke aplikasi dengan NIS ini.', ['required', 'string', 'max:30']),
            new ImportColumn('full_name', 'Nama siswa', 'Rizky Pratama', 'Nama lengkap sesuai buku induk.', ['required', 'string', 'max:255']),
            new ImportColumn('class_name', 'Kelas', '8-A', 'Kelas baru dibuat otomatis bila belum ada.', ['required', 'string', 'max:20']),
            new ImportColumn('birth_date', 'Tanggal lahir', '2011-05-12', 'Format 2011-05-12 atau 12/05/2011.', ['required', 'date_format:Y-m-d', 'before:today']),
            new ImportColumn('parent_name', 'Nama wali', 'Budi Pratama', 'Orang tua atau wali.', ['required', 'string', 'max:255']),
            new ImportColumn('parent_email', 'Email wali', 'budi.pratama@gmail.com', 'Untuk kode OTP saat orang tua lupa kata sandi.', ['required', 'email', 'max:255']),
        ];
    }

    public function exampleRows(): array
    {
        return [
            ['20241010', 'Rizky Pratama', '8-A', '2011-05-12', 'Budi Pratama', 'budi.pratama@gmail.com'],
            ['20241011', 'Siti Nurhaliza', '8-A', '2011-09-30', 'Ahmad Hidayat', 'ahmad.hidayat@gmail.com'],
        ];
    }

    protected function messages(): array
    {
        return [
            'birth_date.date_format' => 'Tanggal lahir ":input" tidak dikenali. Tulis seperti 2011-05-12 atau 12/05/2011.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
        ];
    }

    protected function prepare(array $data): array
    {
        $data['birth_date'] = $this->normalizeDate($data['birth_date']);
        $data['parent_email'] = strtolower($data['parent_email']);

        return $data;
    }

    protected function persist(array $data): bool
    {
        $isNew = ! Student::where('nis', $data['nis'])->exists();

        $student = $this->students->save($data);
        $this->students->linkParent($student, $data['parent_name'], $data['parent_email']);

        return $isNew;
    }
}
