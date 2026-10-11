<?php

namespace Tests\Feature\Admin;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherTest extends AdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsAdmin();
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'nuptk' => '1234567890123456',
            'full_name' => 'Dewi Lestari, S.Pd',
            'email' => 'dewi@example.com',
            'phone_number' => '081234567890',
            'is_active' => '1',
        ];
    }

    public function test_admin_can_add_a_teacher_with_login_account(): void
    {
        $this->post('/admin/teachers', $this->payload())->assertRedirect('/admin/teachers');

        $teacher = Teacher::where('nuptk', '1234567890123456')->firstOrFail();
        $this->assertSame('teacher', $teacher->user->role);
        $this->assertSame('1234567890123456', $teacher->user->username);
        $this->assertTrue(Hash::check('password123', $teacher->user->password));
    }

    public function test_email_already_used_by_another_account_is_rejected(): void
    {
        $this->post('/admin/teachers', $this->payload(['email' => 'admin@example.com']))
            ->assertSessionHasErrors(['email' => 'Email sudah dipakai.']);
    }

    public function test_nuptk_must_be_digits(): void
    {
        $this->post('/admin/teachers', $this->payload(['nuptk' => '12-34']))
            ->assertSessionHasErrors(['nuptk' => 'NUPTK hanya boleh berisi angka.']);
    }

    public function test_editing_updates_account_and_can_reset_password_and_deactivate(): void
    {
        $teacher = $this->makeTeacher('1111222233334444', 'lama@example.com');

        $this->put("/admin/teachers/{$teacher->id}", $this->payload([
            'nuptk' => '5555666677778888',
            'email' => 'baru@example.com',
            'is_active' => '0',
            'reset_password' => '1',
        ]))->assertRedirect('/admin/teachers');

        $user = $teacher->fresh()->user;
        $this->assertSame('baru@example.com', $user->email);
        $this->assertSame('5555666677778888', $user->username);
        $this->assertFalse($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_editing_without_reset_keeps_password(): void
    {
        $teacher = $this->makeTeacher();

        $this->put("/admin/teachers/{$teacher->id}", $this->payload(['nuptk' => $teacher->nuptk, 'email' => 'guru@example.com']));

        $this->assertTrue(Hash::check('secret123', $teacher->fresh()->user->password));
    }

    public function test_teacher_without_history_is_deleted_with_account(): void
    {
        $teacher = $this->makeTeacher();
        $userId = $teacher->user_id;

        $this->delete("/admin/teachers/{$teacher->id}")->assertRedirect('/admin/teachers');

        $this->assertModelMissing($teacher);
        $this->assertNull(User::find($userId));
    }

    public function test_teacher_with_schedule_cannot_be_deleted(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeSchedule($teacher, $this->makeClass());

        $this->delete("/admin/teachers/{$teacher->id}")->assertSessionHas('error');

        $this->assertModelExists($teacher);
    }

    public function test_list_is_searchable_by_name_nuptk_and_email(): void
    {
        $this->makeTeacher('1111000011110000', 'agus@example.com')->update(['full_name' => 'Agus Salim']);
        $this->makeTeacher('2222000022220000', 'dewi@example.com')->update(['full_name' => 'Dewi Sartika']);

        $this->get('/admin/teachers?q=agus')->assertSee('Agus Salim')->assertDontSee('Dewi Sartika');
        $this->get('/admin/teachers?q=22220000')->assertSee('Dewi Sartika')->assertDontSee('Agus Salim');
        $this->get('/admin/teachers?q=dewi@')->assertSee('Dewi Sartika');
        $this->get('/admin/teachers/create')->assertOk();
        $this->get('/admin/teachers/'.Teacher::first()->id.'/edit')->assertOk();
    }
}
