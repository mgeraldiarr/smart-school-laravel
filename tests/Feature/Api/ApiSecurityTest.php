<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\TeacherService;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Admin\AdminTestCase;

/**
 * Fase 0: setiap peran hanya bisa mengakses data miliknya, dan aturan jam
 * presensi mengikuti keputusan tim (scan hanya selama jam pelajaran, koreksi
 * manual sampai akhir hari).
 */
class ApiSecurityTest extends AdminTestCase
{
    private Teacher $teacher;
    private SchoolClass $class;
    private Schedule $schedule;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Senin, 23 November 2026, 07:30 WIB: di dalam jam pelajaran 07:00-08:00.
        Carbon::setTestNow(Carbon::parse('2026-11-23 07:30:00', 'Asia/Jakarta'));

        $this->teacher = $this->makeTeacher();
        $this->class = $this->makeClass('8-A');
        $this->schedule = $this->makeSchedule($this->teacher, $this->class, 'Senin');
        $this->student = $this->makeStudent(['class_id' => $this->class->id, 'full_name' => 'Ahmad Rizki']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ---------- Peran ----------

    public function test_parent_token_cannot_call_teacher_endpoints(): void
    {
        Sanctum::actingAs($this->makeParentUser());

        $this->postJson('/api/teacher/attendance/manual', $this->manualPayload('Alpa'))->assertForbidden();
        $this->getJson('/api/teacher/grades')->assertForbidden();
        $this->postJson('/api/attendance/face-scan', $this->scanPayload())->assertForbidden();
        $this->assertSame(0, Attendance::count());
    }

    public function test_teacher_token_cannot_call_parent_endpoints(): void
    {
        Sanctum::actingAs($this->teacher->user);

        $this->getJson('/api/parent/student-status')->assertForbidden();
    }

    public function test_deactivated_account_cannot_use_old_token(): void
    {
        $this->teacher->user->update(['is_active' => false]);
        Sanctum::actingAs($this->teacher->user->fresh());

        $this->getJson('/api/teacher/schedules')->assertForbidden();
    }

    // ---------- Kepemilikan jadwal & kelas ----------

    public function test_teacher_cannot_open_another_teachers_session(): void
    {
        $other = $this->makeTeacher('9999888877776666', 'lain@example.com');
        Sanctum::actingAs($other->user);

        $this->getJson('/api/teacher/attendance/session?schedule_id='.$this->schedule->id)->assertForbidden();
        $this->postJson('/api/teacher/attendance/manual', $this->manualPayload('Hadir'))->assertForbidden();
    }

    public function test_class_students_only_for_classes_the_teacher_teaches(): void
    {
        $otherClass = $this->makeClass('9-B');
        Sanctum::actingAs($this->teacher->user);

        $this->getJson("/api/teacher/classes/{$this->class->id}/students")->assertOk();
        $this->getJson("/api/teacher/classes/{$otherClass->id}/students")->assertForbidden();
    }

    // ---------- Presensi wajah ----------

    public function test_face_scan_during_lesson_records_attendance_with_photo(): void
    {
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/attendance/face-scan', $this->scanPayload() + [
            'confidence_score' => 0.91,
            'captured_photo' => 'attendance/2026-11-23/ahmad.jpg',
        ])->assertCreated()->assertJsonPath('data.status', 'Hadir');

        $attendance = Attendance::firstOrFail();
        $this->assertSame('2026-11-23', Carbon::parse($attendance->date)->toDateString());
        $this->assertSame('attendance/2026-11-23/ahmad.jpg', $attendance->captured_photo_url);
        $this->assertEqualsWithDelta(0.91, $attendance->confidence_score, 0.001);
        $this->assertSame($this->teacher->id, (int) $attendance->input_by_teacher_id);
    }

    public function test_face_scan_after_lesson_ends_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-23 08:01:00', 'Asia/Jakarta'));
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/attendance/face-scan', $this->scanPayload())->assertStatus(422);
    }

    public function test_face_scan_on_another_day_is_rejected(): void
    {
        // Selasa di jam yang sama: jadwal ini hanya hari Senin.
        Carbon::setTestNow(Carbon::parse('2026-11-24 07:30:00', 'Asia/Jakarta'));
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/attendance/face-scan', $this->scanPayload())->assertStatus(422);
    }

    public function test_face_scan_rejects_student_from_another_class(): void
    {
        $outsider = $this->makeStudent(['class_id' => $this->makeClass('9-C')->id]);
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/attendance/face-scan', [
            'student_id' => $outsider->id,
            'schedule_id' => $this->schedule->id,
        ])->assertStatus(422);
    }

    public function test_second_scan_returns_conflict_with_snake_case_keys(): void
    {
        $this->makeAttendance($this->student, $this->schedule, '2026-11-23');
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/attendance/face-scan', $this->scanPayload())
            ->assertStatus(409)
            ->assertJsonPath('data.student_name', 'Ahmad Rizki')
            ->assertJsonPath('data.check_in_time', '07:05:00');
    }

    // ---------- Presensi manual ----------

    public function test_manual_correction_after_lesson_keeps_original_check_in_time(): void
    {
        $this->makeAttendance($this->student, $this->schedule, '2026-11-23', 'Alpa');
        Carbon::setTestNow(Carbon::parse('2026-11-23 15:00:00', 'Asia/Jakarta'));
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/teacher/attendance/manual', $this->manualPayload('Izin'))
            ->assertOk()
            ->assertJsonPath('data.status', 'Izin');

        $attendance = Attendance::firstOrFail();
        $this->assertSame('Izin', $attendance->status);
        $this->assertSame('07:05:00', $attendance->check_in_time);
    }

    public function test_manual_attendance_for_another_date_is_rejected(): void
    {
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/teacher/attendance/manual', $this->manualPayload('Sakit') + [
            'date' => '2026-11-16',
        ])->assertStatus(422);
    }

    // ---------- Nilai ----------

    public function test_teacher_sees_and_edits_only_their_own_grades(): void
    {
        $other = $this->makeTeacher('9999888877776666', 'lain@example.com');
        $mine = $this->makeGrade($this->teacher);
        $theirs = $this->makeGrade($other);
        Sanctum::actingAs($this->teacher->user);

        $this->getJson('/api/teacher/grades')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);
        $this->putJson("/api/teacher/grades/{$theirs->id}", ['score' => 10])->assertForbidden();
        $this->deleteJson("/api/teacher/grades/{$theirs->id}")->assertForbidden();
        $this->assertEquals(80, $theirs->fresh()->score);
    }

    public function test_teacher_cannot_grade_a_subject_they_do_not_teach(): void
    {
        $otherSubject = Subject::create(['code' => 'IPA-01', 'name' => 'IPA']);
        Sanctum::actingAs($this->teacher->user);

        $this->postJson('/api/teacher/grades', [
            'student_id' => $this->student->id,
            'subject_id' => $otherSubject->id,
            'class_id' => $this->class->id,
            'assessment_type' => 'UTS',
            'title' => 'UTS Ganjil',
            'score' => 90,
        ])->assertForbidden();
    }

    // ---------- Wali murid ----------

    public function test_parent_status_uses_shared_not_recorded_status(): void
    {
        Sanctum::actingAs($this->makeParentUser());

        $this->getJson('/api/parent/student-status')
            ->assertOk()
            ->assertJsonPath('data.schedules_today.0.status', Attendance::NOT_RECORDED);
    }

    // ---------- Ganti kata sandi ----------

    public function test_imported_teacher_must_change_password_on_first_login(): void
    {
        app(TeacherService::class)->save([
            'nuptk' => '5555666677778888',
            'full_name' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
        ]);

        $this->postJson('/api/auth/teacher/login', [
            'nuptk' => '5555666677778888',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('data.user.must_change_password', true);
    }

    public function test_change_password_clears_flag_and_revokes_other_sessions(): void
    {
        $user = $this->teacher->user;
        $user->update(['must_change_password' => true]);
        $user->createToken('hp-lama');
        $current = $user->createToken('hp-ini')->plainTextToken;

        $this->withToken($current)->postJson('/api/auth/change-password', [
            'current_password' => 'salah123',
            'password' => 'sandiBaru123',
            'password_confirmation' => 'sandiBaru123',
        ])->assertStatus(422);

        $this->withToken($current)->postJson('/api/auth/change-password', [
            'current_password' => 'secret123',
            'password' => 'sandiBaru123',
            'password_confirmation' => 'sandiBaru123',
        ])->assertOk()->assertJsonPath('data.must_change_password', false);

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertSame(1, $user->tokens()->count()); // Hanya sesi yang sedang dipakai.

        $this->postJson('/api/auth/teacher/login', [
            'nuptk' => $this->teacher->nuptk,
            'password' => 'sandiBaru123',
        ])->assertOk()->assertJsonPath('data.user.must_change_password', false);
    }

    // ---------- Helper ----------

    private function scanPayload(): array
    {
        return ['student_id' => $this->student->id, 'schedule_id' => $this->schedule->id];
    }

    private function manualPayload(string $status): array
    {
        return $this->scanPayload() + ['status' => $status];
    }

    private function makeParentUser(): User
    {
        $user = User::create([
            'email' => 'wali@example.com',
            'username' => $this->student->nis,
            'password' => 'secret123',
            'role' => 'parent',
            'is_active' => true,
        ]);
        $parent = SchoolParent::create(['user_id' => $user->id, 'full_name' => 'Budi Pratama']);
        $parent->students()->attach($this->student->id);

        return $user;
    }

    private function makeGrade(Teacher $teacher): Grade
    {
        return Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->schedule->subject_id,
            'teacher_id' => $teacher->id,
            'class_id' => $this->class->id,
            'assessment_type' => 'UTS',
            'title' => 'UTS Ganjil',
            'score' => 80,
            'assessment_date' => '2026-11-23',
        ]);
    }
}
