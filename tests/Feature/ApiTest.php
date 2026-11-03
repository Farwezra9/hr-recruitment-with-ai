<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_can_manage_job_postings_through_the_api(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'recruiter']));

        $response = $this->postJson('/api/jobs', [
            'title' => 'Backend Engineer',
            'description' => 'Build reliable services.',
            'requirements' => 'PHP and Laravel experience.',
            'employment_type' => 'full_time',
            'work_mode' => 'remote',
            'salary_min' => 5000000,
            'salary_max' => 10000000,
            'vacancies' => 1,
            'status' => 'draft',
        ]);

        $response->assertCreated()->assertJsonPath('title', 'Backend Engineer');
        $jobId = $response->json('id');

        $this->patchJson("/api/jobs/{$jobId}", ['title' => 'Senior Backend Engineer'])
            ->assertOk()
            ->assertJsonPath('title', 'Senior Backend Engineer');

        $this->patchJson("/api/jobs/{$jobId}", ['salary_min' => 12000000])
            ->assertUnprocessable();

        $this->deleteJson("/api/jobs/{$jobId}")->assertNoContent();
        $this->assertSoftDeleted('job_postings', ['id' => $jobId]);
    }

    public function test_api_requires_authentication_and_recruiter_or_admin_role(): void
    {
        $this->getJson('/api/jobs')->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(['role' => 'hiring_manager']));

        $this->getJson('/api/jobs')->assertForbidden();
    }

    public function test_api_pagination_is_capped(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/jobs?per_page=101')->assertUnprocessable();
    }
}
