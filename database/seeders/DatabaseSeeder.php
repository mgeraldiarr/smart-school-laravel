<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\SchoolParent;
use App\Models\Schedule;
use App\Models\Grade;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin Sekolah (TU)
        User::create([
            'email' => 'admin@smartschool.sch.id',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Data Kelas (Rombel)
        $class8A = SchoolClass::create([
            'name' => '8-A',
            'grade' => '8',
            'academic_year' => '2026/2027',
        ]);

        // 3. Data Mata Pelajaran
        $matematika = Subject::create([
            'name' => 'Matematika',
            'code' => 'MTK-01',
        ]);

        $bIndo = Subject::create([
            'name' => 'Bahasa Indonesia',
            'code' => 'BIN-01',
        ]);

        // 4. Akun Guru (Pak Bambang) dengan NUPTK
        $userGuru = User::create([
            'email' => 'bambang@gmail.com',
            'username' => '198501152010011002', // NUPTK untuk Login
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $guru = Teacher::create([
            'user_id' => $userGuru->id,
            'nuptk' => '198501152010011002',
            'full_name' => 'Bambang Sutrisno, S.Pd',
            'phone_number' => '081234567890',
        ]);

        // 5. Data Siswa
        $siswa1 = Student::create([
            'nis' => '20241001',
            'full_name' => 'Ahmad Fauzi',
            'birth_date' => '2011-08-15',
            'class_id' => $class8A->id,
            'is_active' => true,
        ]);

        $siswa2 = Student::create([
            'nis' => '20241002',
            'full_name' => 'Bunga Citra Lestari',
            'birth_date' => '2011-12-04',
            'class_id' => $class8A->id,
            'is_active' => true,
        ]);

        // 6. Akun Orang Tua (Wali Ahmad Fauzi) dengan NIS
        $userOrtu = User::create([
            'email' => 'geraldiarisyi27@gmail.com', // Gmail Anda untuk testing OTP
            'username' => '20241001', // Login via NIS Murid
            'password' => Hash::make('password123'),
            'role' => 'parent',
            'is_active' => true,
        ]);

        $ortu = SchoolParent::create([
            'user_id' => $userOrtu->id,
            'full_name' => 'Hendra Fauzi',
            'phone_number' => '081298765432',
        ]);

        // Menautkan Orang Tua ke Siswa (Ahmad Fauzi)
        $ortu->students()->attach($siswa1->id);

        // 7. Jadwal Pelajaran (Senin di Kelas 8-A)
        $jadwal1 = Schedule::create([
            'class_id' => $class8A->id,
            'subject_id' => $matematika->id,
            'teacher_id' => $guru->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
        ]);

        Schedule::create([
            'class_id' => $class8A->id,
            'subject_id' => $bIndo->id,
            'teacher_id' => $guru->id,
            'day_of_week' => 'Senin',
            'start_time' => '09:15:00',
            'end_time' => '10:45:00',
        ]);

        // 8. Contoh Nilai Akademik Siswa
        Grade::create([
            'student_id' => $siswa1->id,
            'subject_id' => $matematika->id,
            'teacher_id' => $guru->id,
            'class_id' => $class8A->id,
            'assessment_type' => 'Tugas',
            'title' => 'Tugas 1 Persamaan Linier',
            'score' => 88.00,
            'teacher_notes' => 'Pemahaman aljabar sangat baik.',
            'assessment_date' => now()->toDateString(),
        ]);

        Grade::create([
            'student_id' => $siswa1->id,
            'subject_id' => $matematika->id,
            'teacher_id' => $guru->id,
            'class_id' => $class8A->id,
            'assessment_type' => 'UTS',
            'title' => 'UTS Matematika Semester Ganjil',
            'score' => 90.00,
            'teacher_notes' => 'Pertahankan nilai dan fokus.',
            'assessment_date' => now()->toDateString(),
        ]);
    }
}
