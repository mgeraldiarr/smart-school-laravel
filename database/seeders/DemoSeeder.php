<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh berskala sekolah (9 rombel, ±250 siswa, 30 hari presensi, nilai)
 * untuk mencoba dashboard. Jalankan di database kosong:
 *
 *   php artisan migrate:fresh --seed --seeder=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    private const SUBJECTS = [
        'MTK' => 'Matematika', 'BIN' => 'Bahasa Indonesia', 'BIG' => 'Bahasa Inggris', 'IPA' => 'IPA',
        'IPS' => 'IPS', 'PKN' => 'Pendidikan Pancasila', 'PAI' => 'Pendidikan Agama', 'PJK' => 'PJOK',
    ];

    private const DAYS = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $faker->seed(2026);
        mt_srand(2026);
        $now = now();
        $password = Hash::make('password123');

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@smartschool.sch.id'],
            ['username' => 'admin', 'password' => Hash::make('admin123'), 'role' => 'admin', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        );

        $subjects = [];
        foreach (self::SUBJECTS as $code => $name) {
            $subjects[] = DB::table('subjects')->insertGetId(['name' => $name, 'code' => "{$code}-01", 'created_at' => $now, 'updated_at' => $now]);
        }

        // Tiap guru punya satu mata pelajaran; beberapa mapel diampu dua guru.
        $teachers = [];
        foreach (range(0, 11) as $i) {
            $nuptk = (string) $faker->unique()->numerify('################');
            $userId = DB::table('users')->insertGetId([
                'email' => $faker->unique()->userName().'@gmail.com', 'username' => $nuptk, 'password' => $password,
                'role' => 'teacher', 'is_active' => $i !== 11, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $teachers[] = [
                'id' => DB::table('teachers')->insertGetId([
                    'user_id' => $userId, 'nuptk' => $nuptk,
                    'full_name' => $faker->firstName().' '.$faker->lastName().', '.$faker->randomElement(['S.Pd', 'S.Pd', 'M.Pd', 'S.Si']),
                    'phone_number' => $faker->numerify('08##########'), 'created_at' => $now, 'updated_at' => $now,
                ]),
                'subject' => $subjects[$i % count($subjects)],
                // Seberapa rajin guru ini mengisi presensi.
                'diligence' => [0.98, 0.95, 0.9, 0.97, 0.7, 0.93, 0.99, 0.85, 0.96, 0.6, 0.92, 0.8][$i],
            ];
        }

        $schedules = [];
        $students = [];
        foreach ([7, 8, 9] as $grade) {
            foreach (['A', 'B', 'C'] as $letter) {
                $classId = DB::table('school_classes')->insertGetId([
                    'name' => "{$grade}-{$letter}", 'grade' => (string) $grade, 'academic_year' => '2026/2027',
                    'created_at' => $now, 'updated_at' => $now,
                ]);

                foreach (range(1, mt_rand(26, 30)) as $n) {
                    $students[$classId][] = DB::table('students')->insertGetId([
                        'nis' => sprintf('2026%d%s%02d', $grade, ord($letter) - 64, $n),
                        'full_name' => $faker->firstName().' '.$faker->lastName(),
                        'birth_date' => $faker->dateTimeBetween('-'.(12 + $grade - 6).' years', '-'.(11 + $grade - 6).' years')->format('Y-m-d'),
                        'class_id' => $classId, 'is_active' => $n !== 1 || $letter !== 'C',
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                foreach (self::DAYS as $dayNumber => $day) {
                    foreach (range(0, $dayNumber === 6 ? 2 : 4) as $slot) {
                        $teacher = $teachers[($classId + $dayNumber * 3 + $slot) % count($teachers)];
                        $start = Carbon::createFromTime(7, 0)->addMinutes($slot * 80);
                        $schedules[] = [
                            'id' => DB::table('schedules')->insertGetId([
                                'class_id' => $classId, 'subject_id' => $teacher['subject'], 'teacher_id' => $teacher['id'],
                                'day_of_week' => $day, 'start_time' => $start->format('H:i:s'), 'end_time' => $start->copy()->addMinutes(70)->format('H:i:s'),
                                'created_at' => $now, 'updated_at' => $now,
                            ]),
                            'class' => $classId, 'day' => $day, 'teacher' => $teacher, 'start' => $start->format('H:i:s'),
                        ];
                    }
                }
            }
        }

        $this->seedAttendances($schedules, $students, $now);
        $this->seedGrades($teachers, $schedules, $students, $now);
    }

    private function seedAttendances(array $schedules, array $students, Carbon $now): void
    {
        $rows = [];
        for ($date = today()->subDays(29); $date->lte(today()); $date->addDay()) {
            if ($date->isSunday()) {
                continue;
            }

            // Status siswa ditentukan per hari, lalu dipakai di semua jam pelajaran.
            $dailyStatus = [];
            foreach ($students as $classStudents) {
                foreach ($classStudents as $studentId) {
                    $roll = mt_rand(1, 100);
                    $dailyStatus[$studentId] = match (true) {
                        $roll <= 91 => 'Hadir', $roll <= 95 => 'Sakit', $roll <= 98 => 'Izin', default => 'Alpa',
                    };
                }
            }

            foreach ($schedules as $schedule) {
                $isToday = $date->isToday();
                if ($schedule['day'] !== self::DAYS[$date->dayOfWeekIso]
                    || ($isToday && $schedule['start'] > now()->format('H:i:s'))
                    || mt_rand(1, 1000) > $schedule['teacher']['diligence'] * 1000) {
                    continue;
                }

                foreach ($students[$schedule['class']] as $studentId) {
                    $status = $dailyStatus[$studentId];
                    $manual = $status !== 'Hadir' || mt_rand(1, 100) <= 6;
                    $rows[] = [
                        'student_id' => $studentId, 'schedule_id' => $schedule['id'], 'date' => $date->toDateString(),
                        'check_in_time' => $schedule['start'], 'status' => $status,
                        'method' => $manual ? 'manual_teacher' : 'face_scan',
                        'input_by_teacher_id' => $manual ? $schedule['teacher']['id'] : null,
                        'confidence_score' => $manual ? null : mt_rand(880, 990) / 1000,
                        'created_at' => $date->copy()->setTimeFromTimeString($schedule['start']), 'updated_at' => $now,
                    ];
                }

                if (count($rows) >= 500) {
                    DB::table('attendances')->insert($rows);
                    $rows = [];
                }
            }
        }

        DB::table('attendances')->insert($rows);
    }

    private function seedGrades(array $teachers, array $schedules, array $students, Carbon $now): void
    {
        // Rata-rata dasar per mapel agar grafik tidak datar.
        $difficulty = array_combine(array_column($teachers, 'subject'), array_map(fn () => mt_rand(72, 86), $teachers));

        $rows = [];
        foreach (collect($schedules)->unique(fn ($s) => $s['class'].'-'.$s['teacher']['subject']) as $schedule) {
            foreach ($students[$schedule['class']] as $studentId) {
                $ability = mt_rand(-12, 12);
                foreach ([['UAS', 'UAS Semester Genap 2025/2026', 110], ['Tugas', 'Tugas 1', 25], ['Tugas', 'Tugas 2', 18], ['UTS', 'UTS Semester Ganjil', 8]] as [$type, $title, $daysAgo]) {
                    $score = $difficulty[$schedule['teacher']['subject']] + $ability + mt_rand(-8, 8) + match ($type) { 'Tugas' => 4, 'UAS' => -3, default => 0 };
                    $rows[] = [
                        'student_id' => $studentId, 'subject_id' => $schedule['teacher']['subject'], 'teacher_id' => $schedule['teacher']['id'],
                        'class_id' => $schedule['class'], 'assessment_type' => $type, 'title' => $title,
                        'score' => max(40, min(100, $score)), 'teacher_notes' => null,
                        'assessment_date' => today()->subDays($daysAgo)->toDateString(), 'created_at' => $now, 'updated_at' => $now,
                    ];
                }
            }

            DB::table('grades')->insert($rows);
            $rows = [];
        }
    }
}
