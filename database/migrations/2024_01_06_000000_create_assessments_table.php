<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['technical', 'psychometric', 'language', 'case_study', 'other'])->default('technical');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->unsignedTinyInteger('passing_score')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->text('question');
            $table->enum('question_type', ['multiple_choice', 'essay'])->default('multiple_choice');
            $table->json('options')->nullable(); // for multiple_choice: ["A...", "B...", ...]
            $table->string('correct_option')->nullable(); // e.g. "A"
            $table->unsignedTinyInteger('points')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('candidate_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('assessments');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'expired'])->default('assigned');
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_assessments');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};
