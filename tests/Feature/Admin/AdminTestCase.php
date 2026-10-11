<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected function signInAsAdmin(): User
    {
        $admin = User::create([
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    protected function makeClass(string $name = '8-A'): SchoolClass
    {
        return SchoolClass::create(['name' => $name, 'grade' => '8', 'academic_year' => '2026/2027']);
    }

    protected function makeStudent(array $attributes = []): Student
    {
        return Student::create($attributes + [
            'nis' => (string) fake()->unique()->numerify('2026####'),
            'full_name' => fake()->name(),
            'birth_date' => '2012-01-01',
            'class_id' => $attributes['class_id'] ?? $this->makeClass(fake()->unique()->bothify('#-?'))->id,
            'is_active' => true,
        ]);
    }

    protected function makeTeacher(string $nuptk = '1111222233334444', string $email = 'guru@example.com'): Teacher
    {
        $user = User::create([
            'email' => $email,
            'username' => $nuptk,
            'password' => 'secret123',
            'role' => 'teacher',
            'is_active' => true,
        ]);

        return Teacher::create(['user_id' => $user->id, 'nuptk' => $nuptk, 'full_name' => 'Guru '.$nuptk]);
    }

    protected function makeSchedule(Teacher $teacher, SchoolClass $class, string $day = 'Senin'): Schedule
    {
        $subject = Subject::firstOrCreate(['code' => 'MTK-01'], ['name' => 'Matematika']);

        return Schedule::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => $day,
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
        ]);
    }

    protected function makeAttendance(Student $student, Schedule $schedule, string $date, string $status = 'Hadir'): Attendance
    {
        return Attendance::create([
            'student_id' => $student->id,
            'schedule_id' => $schedule->id,
            'date' => $date,
            'check_in_time' => '07:05:00',
            'status' => $status,
            'method' => 'face_scan',
        ]);
    }
}
