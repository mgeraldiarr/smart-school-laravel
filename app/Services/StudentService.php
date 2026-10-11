<?php

namespace App\Services;

use App\Exceptions\DataConflict;
use App\Imports\SpreadsheetImport;
use App\Models\SchoolClass;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\User;

class StudentService
{
    public const DEFAULT_ACADEMIC_YEAR = '2026/2027';

    /**
     * Buat atau perbarui siswa berdasarkan NIS.
     *
     * @param  array{nis: string, full_name: string, class_name: string, birth_date: string, is_active?: bool}  $data
     */
    public function save(array $data, ?Student $student = null): Student
    {
        $student ??= Student::firstOrNew(['nis' => $data['nis']]);

        $student->fill([
            'nis' => $data['nis'],
            'full_name' => $data['full_name'],
            'birth_date' => $data['birth_date'],
            'class_id' => $this->resolveClass($data['class_name'])->id,
            'is_active' => $data['is_active'] ?? true,
        ])->save();

        return $student;
    }

    /** Kelas dicari berdasarkan nama; dibuat otomatis bila belum ada. */
    public function resolveClass(string $name): SchoolClass
    {
        return SchoolClass::firstOrCreate(
            ['name' => $name],
            [
                // "8-A" berarti tingkat 8; tanpa angka di depan dianggap tingkat 8.
                'grade' => preg_match('/^\d+/', $name, $m) ? $m[0] : '8',
                'academic_year' => self::DEFAULT_ACADEMIC_YEAR,
            ],
        );
    }

    /**
     * Tautkan siswa ke akun orang tua berdasarkan email. Akun dibuat bila belum
     * ada; satu akun orang tua boleh tertaut ke beberapa siswa (kakak-adik).
     *
     * @throws DataConflict jika email sudah dipakai akun guru atau admin
     */
    public function linkParent(Student $student, string $parentName, string $email): SchoolParent
    {
        $user = User::where('email', $email)->first();

        if ($user && $user->role !== 'parent') {
            throw new DataConflict("Email {$email} sudah dipakai akun ".($user->role === 'teacher' ? 'guru' : 'admin').'.');
        }

        $user ??= User::create([
            'email' => $email,
            // Orang tua masuk dengan NIS anaknya; bila NIS sudah jadi username akun lain,
            // login tetap bisa lewat relasi parent_students.
            'username' => User::where('username', $student->nis)->exists() ? null : $student->nis,
            'password' => SpreadsheetImport::DEFAULT_PASSWORD,
            'role' => 'parent',
            'is_active' => true,
        ]);

        $parent = SchoolParent::updateOrCreate(['user_id' => $user->id], ['full_name' => $parentName]);
        $parent->students()->syncWithoutDetaching([$student->id]);

        return $parent;
    }

    /** Siswa yang sudah punya presensi atau nilai tidak boleh dihapus permanen. */
    public function hasHistory(Student $student): bool
    {
        return $student->attendances()->exists() || $student->grades()->exists();
    }
}
