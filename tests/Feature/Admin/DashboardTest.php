<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\Subject;
use App\Services\DashboardStats;
use Illuminate\Support\Carbon;

class DashboardTest extends AdminTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_and_grade_pages_render_with_data(): void
    {
        Carbon::setTestNow('2026-10-14 10:00'); // Rabu
        $this->signInAsAdmin();

        $class = $this->makeClass();
        $teacher = $this->makeTeacher();
        $schedule = $this->makeSchedule($teacher, $class, 'Rabu');
        $student = $this->makeStudent(['class_id' => $class->id]);
        $this->makeAttendance($student, $schedule, '2026-10-14');

        Grade::create([
            'student_id' => $student->id, 'subject_id' => Subject::first()->id, 'teacher_id' => $teacher->id,
            'class_id' => $class->id, 'assessment_type' => 'UTS', 'title' => 'UTS', 'score' => 60, 'assessment_date' => '2026-10-10',
        ]);

        $this->get('/admin/dashboard')->assertOk()->assertSee('Kehadiran hari ini')->assertSee('100%');
        $this->get('/admin/grades')->assertOk()->assertSee('Matematika')->assertSee($student->full_name);
        $this->get('/admin/grades?kelas='.$class->id)->assertOk();
    }

    public function test_empty_school_still_renders(): void
    {
        $this->signInAsAdmin();

        $this->get('/admin/dashboard')->assertOk()->assertSee('Belum ada presensi');
        $this->get('/admin/grades')->assertOk()->assertSee('Belum ada nilai');
    }

    public function test_status_counts_unique_active_students_only(): void
    {
        $class = $this->makeClass();
        $schedule = $this->makeSchedule($this->makeTeacher(), $class);
        $second = $this->makeSchedule($this->makeTeacher('2222333344445555', 'b@example.com'), $class);
        $active = $this->makeStudent(['class_id' => $class->id]);
        $inactive = $this->makeStudent(['class_id' => $class->id, 'is_active' => false]);

        // Dua jam pelajaran untuk siswa yang sama dihitung sekali.
        $this->makeAttendance($active, $schedule, '2026-10-12');
        $this->makeAttendance($active, $second, '2026-10-12');
        $this->makeAttendance($inactive, $schedule, '2026-10-12');

        $counts = app(DashboardStats::class)->statusOn(Carbon::parse('2026-10-12'));

        $this->assertSame(['Hadir' => 1, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0], $counts->all());
    }

    public function test_sunday_falls_back_to_last_school_day(): void
    {
        Carbon::setTestNow('2026-10-11 09:00'); // Minggu
        $class = $this->makeClass();
        $this->makeAttendance($this->makeStudent(['class_id' => $class->id]), $this->makeSchedule($this->makeTeacher(), $class, 'Sabtu'), '2026-10-10');

        $stats = app(DashboardStats::class);

        $this->assertSame('2026-10-10', $stats->focusDate()->toDateString());
        $this->assertSame('2026-10-09', $stats->previousSchoolDay(Carbon::parse('2026-10-10'))->toDateString());
    }

    public function test_sunday_with_activity_stays_on_today(): void
    {
        Carbon::setTestNow('2026-10-11 09:00'); // Minggu, ada kegiatan
        $class = $this->makeClass();
        $this->makeAttendance($this->makeStudent(['class_id' => $class->id]), $this->makeSchedule($this->makeTeacher(), $class), '2026-10-11');

        $this->assertTrue(app(DashboardStats::class)->focusDate()->isToday());
    }

    public function test_teacher_compliance_compares_recorded_sessions_with_schedule(): void
    {
        Carbon::setTestNow('2026-10-14 10:00'); // Rabu; periode 30 hari (15 Sep - 14 Okt) memuat 4 hari Senin
        $class = $this->makeClass();
        $teacher = $this->makeTeacher();
        $monday = $this->makeSchedule($teacher, $class, 'Senin');
        $student = $this->makeStudent(['class_id' => $class->id]);
        $other = $this->makeStudent(['class_id' => $class->id]);

        // Satu sesi tercatat oleh dua siswa tetap dihitung satu sesi.
        $this->makeAttendance($student, $monday, '2026-10-12');
        $this->makeAttendance($other, $monday, '2026-10-12');
        $this->makeAttendance($student, $monday, '2026-10-05');

        $row = app(DashboardStats::class)->teacherCompliance()->firstWhere('id', $teacher->id);

        $this->assertSame(4, $row->expected);
        $this->assertSame(2, $row->recorded);
        $this->assertSame(50.0, $row->rate);
    }
}
