<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
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

    protected function makeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => $role . '@example.com',
            'password' => 'secret-password', 'role' => $role, 'is_active' => true,
        ]);
    }

    protected function jobPayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Backend Developer',
            'description' => 'Deskripsi',
            'requirements' => 'PHP, Laravel',
            'employment_type' => 'full_time',
            'work_mode' => 'hybrid',
            'vacancies' => 2,
            'status' => 'open',
        ];
    }

    public function test_unauthenticated_request_gets_json_401(): void
    {
        $this->getJson('/api/v1/jobs')->assertStatus(401)->assertJsonStructure(['message']);
    }

    public function test_login_returns_bearer_token(): void
    {
        $this->makeUser('recruiter');

        $this->postJson('/api/v1/auth/login', ['email' => 'recruiter@example.com', 'password' => 'secret-password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email', 'role']]);

        $this->postJson('/api/v1/auth/login', ['email' => 'recruiter@example.com', 'password' => 'salah'])
            ->assertStatus(401);
    }

    public function test_recruiter_can_crud_jobs_with_proper_status_codes(): void
    {
        Sanctum::actingAs($this->makeUser('recruiter'));

        $id = $this->postJson('/api/v1/jobs', $this->jobPayload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Backend Developer')
            ->json('data.id');

        $this->getJson("/api/v1/jobs/{$id}")->assertOk();
        $this->patchJson("/api/v1/jobs/{$id}", ['status' => 'closed'])->assertOk()->assertJsonPath('data.status', 'closed');
        $this->deleteJson("/api/v1/jobs/{$id}")->assertNoContent();
        $this->getJson("/api/v1/jobs/{$id}")->assertNotFound();
        $this->postJson("/api/v1/jobs/{$id}/restore")->assertOk();
    }

    public function test_validation_errors_return_422_json(): void
    {
        Sanctum::actingAs($this->makeUser('recruiter'));

        $this->postJson('/api/v1/jobs', [])->assertStatus(422)->assertJsonValidationErrors(['title', 'requirements']);
    }

    public function test_roles_are_enforced(): void
    {
        Sanctum::actingAs($this->makeUser('interviewer'));
        $this->getJson('/api/v1/jobs')->assertForbidden();

        Sanctum::actingAs($this->makeUser('hiring_manager'));
        $this->postJson('/api/v1/jobs', $this->jobPayload())->assertForbidden();
        $this->getJson('/api/v1/jobs')->assertOk();
    }

    public function test_public_apply_creates_application_and_rejects_duplicate(): void
    {
        Storage::fake('local');
        Queue::fake();

        $creator = $this->makeUser('recruiter');
        $job = JobPosting::create($this->jobPayload() + ['created_by' => $creator->id]);

        $payload = fn () => [
            'name' => 'Budi', 'email' => 'budi@example.com', 'consent' => '1',
            'cv' => UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n%%EOF"),
        ];

        $this->post("/api/v1/public/jobs/{$job->id}/applications", $payload(), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('message', 'Lamaran diterima.');

        $this->post("/api/v1/public/jobs/{$job->id}/applications", $payload(), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
