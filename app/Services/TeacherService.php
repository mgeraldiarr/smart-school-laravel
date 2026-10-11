<?php

namespace App\Services;

use App\Exceptions\DataConflict;
use App\Imports\SpreadsheetImport;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Teacher;
use App\Models\User;

class TeacherService
{
    /**
     * Buat atau perbarui guru beserta akun login-nya. Guru masuk ke aplikasi
     * dengan NUPTK, jadi username akun selalu disamakan dengan NUPTK.
     *
     * @param  array{nuptk: string, full_name: string, email: string, phone_number?: ?string, is_active?: bool, reset_password?: bool}  $data
     *
     * @throws DataConflict jika email atau NUPTK sudah dipakai akun lain
     */
    public function save(array $data, ?Teacher $teacher = null): Teacher
    {
        $teacher ??= Teacher::firstOrNew(['nuptk' => $data['nuptk']]);
        $user = $teacher->user ?? new User(['role' => 'teacher']);

        $this->ensureUnique('email', $data['email'], $user, "Email {$data['email']} sudah dipakai akun lain.");
        $this->ensureUnique('username', $data['nuptk'], $user, "NUPTK {$data['nuptk']} sudah dipakai sebagai username akun lain.");

        $user->fill([
            'email' => $data['email'],
            'username' => $data['nuptk'],
            'is_active' => $data['is_active'] ?? $user->is_active ?? true,
        ]);

        if (! $user->exists || ($data['reset_password'] ?? false)) {
            $user->password = SpreadsheetImport::DEFAULT_PASSWORD;
        }

        $user->save();

        $teacher->fill([
            'user_id' => $user->id,
            'nuptk' => $data['nuptk'],
            'full_name' => $data['full_name'],
            'phone_number' => ($data['phone_number'] ?? null) ?: null,
        ])->save();

        return $teacher;
    }

    /**
     * Menghapus guru ikut menghapus jadwal, presensi, dan nilai yang terkait
     * (cascade di database), jadi hanya guru tanpa riwayat yang boleh dihapus.
     */
    public function hasHistory(Teacher $teacher): bool
    {
        return $teacher->schedules()->exists()
            || Grade::where('teacher_id', $teacher->id)->exists()
            || Attendance::where('input_by_teacher_id', $teacher->id)->exists();
    }

    public function delete(Teacher $teacher): void
    {
        // teachers.user_id cascade: menghapus akun ikut menghapus data guru.
        $teacher->user ? $teacher->user->delete() : $teacher->delete();
    }

    private function ensureUnique(string $column, string $value, User $user, string $message): void
    {
        $taken = User::where($column, $value)
            ->when($user->exists, fn ($q) => $q->whereKeyNot($user->id))
            ->exists();

        if ($taken) {
            throw new DataConflict($message);
        }
    }
}
