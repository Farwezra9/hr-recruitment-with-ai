<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** PENGAMAN: hanya jalan di SQLite supaya database asli tidak terhapus (lihat phpunit.xml). */
    protected function setUpTraits()
    {
        if (config('database.default') !== 'sqlite') {
            $this->markTestSkipped('Tes dilewati: DB_CONNECTION harus sqlite di phpunit.xml.');
        }

        return parent::setUpTraits();
    }

    protected function makeUser(string $role, string $password = 'Secret-Pass-123'): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => $role . '@example.com',
            'password' => $password, 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_security_headers_are_sent(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_authenticated_pages_are_not_cached(): void
    {
        $this->actingAs($this->makeUser('recruiter'))
            ->get('/jobs')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_weak_password_is_rejected_on_change(): void
    {
        $user = $this->makeUser('recruiter');

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'Secret-Pass-123',
                'password' => 'pendek1',
                'password_confirmation' => 'pendek1',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_login_is_locked_after_repeated_failures_and_audited(): void
    {
        $this->makeUser('recruiter');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'recruiter@example.com', 'password' => 'salah']);
        }

        $this->post('/login', ['email' => 'recruiter@example.com', 'password' => 'Secret-Pass-123'])
            ->assertSessionHasErrors('email');

        $this->assertGreaterThan(0, AuditLog::where('action', 'auth.login_failed')->count());
        $this->assertGreaterThan(0, AuditLog::where('action', 'auth.lockout')->count());
    }

    public function test_only_admin_can_view_audit_log(): void
    {
        $this->actingAs($this->makeUser('recruiter'))->get('/audit-log')->assertForbidden();
        $this->actingAs($this->makeUser('admin'))->get('/audit-log')->assertOk();
    }

    public function test_api_login_lockout_returns_429(): void
    {
        $this->makeUser('recruiter');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'recruiter@example.com', 'password' => 'salah'])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'recruiter@example.com', 'password' => 'Secret-Pass-123'])->assertStatus(429);
    }
}
