<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();

            // Pipeline stage
            $table->enum('stage', [
                'applied',
                'ai_screening',
                'shortlisted',
                'assessment',
                'interview',
                'offer',
                'hired',
                'rejected',
            ])->default('applied');

            $table->enum('status', ['active', 'rejected', 'withdrawn', 'hired'])->default('active');

            // AI screening results (from Claude API)
            $table->unsignedTinyInteger('ai_score')->nullable(); // 0-100 match score
            $table->text('ai_summary')->nullable(); // short human-readable summary
            $table->json('ai_analysis')->nullable(); // structured breakdown: strengths, gaps, red_flags, skills_matched
            $table->timestamp('ai_screened_at')->nullable();
            $table->enum('ai_recommendation', ['strong_fit', 'possible_fit', 'not_a_fit'])->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->unique(['candidate_id', 'job_posting_id']);
        });

        Schema::create('application_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('from_stage')->nullable();
            $table->string('to_stage');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_stage_histories');
        Schema::dropIfExists('applications');
    }
};
