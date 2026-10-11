<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'email' => "{$role}@example.com",
            'username' => $role,
            'password' => 'secret123',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_in_and_see_every_page(): void
    {
        $this->user('admin');

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'secret123'])
            ->assertRedirect('/admin/dashboard');

        foreach (['/admin/dashboard', '/admin/students', '/admin/teachers', '/admin/grades', '/admin/import/siswa', '/admin/import/guru', '/admin/search?q=ab'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_teacher_cannot_log_in_to_admin_panel(): void
    {
        $this->user('teacher');

        $this->post('/admin/login', ['email' => 'teacher@example.com', 'password' => 'secret123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_admin_session_is_forbidden(): void
    {
        $this->actingAs($this->user('parent'))
            ->get('/admin/dashboard')
            ->assertForbidden();
    }
}
