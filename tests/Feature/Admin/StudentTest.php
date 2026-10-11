<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

class StudentTest extends AdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsAdmin();
    }

    public function test_admin_can_add_a_student_with_a_new_class_and_parent_account(): void
    {
        $this->post('/admin/students', [
            'nis' => '20260001',
            'full_name' => 'Rizky Pratama',
            'class_name' => '9-B',
            'birth_date' => '2011-05-12',
            'is_active' => '1',
            'parent_name' => 'Budi Pratama',
            'parent_email' => 'Budi@Example.com',
        ])->assertRedirect('/admin/students')->assertSessionHas('success');

        $student = Student::where('nis', '20260001')->firstOrFail();
        $this->assertSame('9-B', $student->schoolClass->name);
        $this->assertSame('budi@example.com', $student->parents->first()->user->email);
        $this->assertSame('20260001', $student->parents->first()->user->username);
    }

    public function test_parent_details_are_required_when_adding(): void
    {
        $this->post('/admin/students', [
            'nis' => '20260002', 'full_name' => 'Tanpa Wali', 'class_name' => '8-A', 'birth_date' => '2011-05-12',
        ])->assertSessionHasErrors(['parent_name' => 'Nama wali wajib diisi.', 'parent_email']);
    }

    public function test_nis_must_be_unique(): void
    {
        $existing = $this->makeStudent(['nis' => '20260003']);

        $this->post('/admin/students', [
            'nis' => '20260003', 'full_name' => 'Kembar', 'class_name' => '8-A', 'birth_date' => '2011-05-12',
            'parent_name' => 'Wali', 'parent_email' => 'wali@example.com',
        ])->assertSessionHasErrors(['nis' => 'NIS sudah dipakai.']);

        // Mengubah siswa itu sendiri dengan NIS yang sama tetap boleh.
        $this->put("/admin/students/{$existing->id}", [
            'nis' => '20260003', 'full_name' => 'Nama Baru', 'class_name' => '8-A', 'birth_date' => '2011-05-12',
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_can_deactivate_a_student(): void
    {
        $student = $this->makeStudent();

        $this->put("/admin/students/{$student->id}", [
            'nis' => $student->nis, 'full_name' => $student->full_name, 'class_name' => '8-A',
            'birth_date' => '2012-01-01', 'is_active' => '0',
        ])->assertRedirect('/admin/students');

        $this->assertFalse((bool) $student->fresh()->is_active);
    }

    public function test_parent_email_owned_by_admin_is_a_validation_error(): void
    {
        $student = $this->makeStudent();

        $this->put("/admin/students/{$student->id}", [
            'nis' => $student->nis, 'full_name' => $student->full_name, 'class_name' => '8-A', 'birth_date' => '2012-01-01',
            'parent_name' => 'Wali', 'parent_email' => 'admin@example.com',
        ])->assertSessionHasErrors(['parent_email' => 'Email admin@example.com sudah dipakai akun admin.']);
    }

    public function test_student_without_history_can_be_deleted(): void
    {
        $student = $this->makeStudent();

        $this->delete("/admin/students/{$student->id}")->assertRedirect('/admin/students');

        $this->assertModelMissing($student);
    }

    public function test_student_with_attendance_cannot_be_deleted(): void
    {
        $class = $this->makeClass();
        $student = $this->makeStudent(['class_id' => $class->id]);
        $this->makeAttendance($student, $this->makeSchedule($this->makeTeacher(), $class), today()->toDateString());

        $this->delete("/admin/students/{$student->id}")->assertSessionHas('error');

        $this->assertModelExists($student);
    }

    public function test_list_can_be_searched_and_filtered(): void
    {
        $a = $this->makeClass('7-A');
        $b = $this->makeClass('7-B');
        $this->makeStudent(['full_name' => 'Ahmad Fauzi', 'class_id' => $a->id]);
        $this->makeStudent(['full_name' => 'Ahmad Dahlan', 'class_id' => $b->id, 'is_active' => false]);
        $this->makeStudent(['full_name' => 'Bunga Citra', 'class_id' => $a->id, 'nis' => '20269999']);

        $this->get('/admin/students?q=ahmad')->assertSee('Ahmad Fauzi')->assertSee('Ahmad Dahlan')->assertDontSee('Bunga Citra');
        $this->get('/admin/students?q=ahmad&kelas='.$a->id)->assertSee('Ahmad Fauzi')->assertDontSee('Ahmad Dahlan');
        $this->get('/admin/students?status=nonaktif')->assertSee('Ahmad Dahlan')->assertDontSee('Ahmad Fauzi');
        $this->get('/admin/students?q=20269999')->assertSee('Bunga Citra')->assertDontSee('Ahmad');
    }

    public function test_global_search_finds_students_and_teachers(): void
    {
        $this->makeStudent(['full_name' => 'Siti Rahma']);
        $this->makeTeacher()->update(['full_name' => 'Rahmat Hidayat, S.Pd']);

        $this->get('/admin/search?q=rahm')->assertOk()->assertSee('Siti Rahma')->assertSee('Rahmat Hidayat, S.Pd');
        $this->get('/admin/search?q=r')->assertOk()->assertSee('minimal 2 huruf');
    }

    public function test_forms_render(): void
    {
        $student = $this->makeStudent();

        $this->get('/admin/students')->assertOk();
        $this->get('/admin/students/create')->assertOk();
        $this->get("/admin/students/{$student->id}/edit")->assertOk()->assertSee($student->full_name);
    }
}
