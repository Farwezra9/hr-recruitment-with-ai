<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Persetujuan pelamar atas pemrosesan data
        Schema::table('candidates', function (Blueprint $table) {
            $table->timestamp('consented_at')->nullable();
        });

        // Lowongan yang dihapus bisa dipulihkan (soft delete)
        Schema::table('job_postings', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Link tes pakai token acak + masa berlaku + penilaian esai
        Schema::table('candidate_assessments', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique();
            $table->dateTime('expires_at')->nullable();
            $table->json('essay_scores')->nullable();
            $table->dateTime('graded_at')->nullable();
        });

        // Isi token untuk tes yang sudah terlanjur ditugaskan sebelum update ini
        DB::table('candidate_assessments')->whereNull('token')->orderBy('id')->each(function ($row) {
            DB::table('candidate_assessments')->where('id', $row->id)->update([
                'token' => Str::random(40),
                'expires_at' => now()->addDays(7),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('candidate_assessments', function (Blueprint $table) {
            $table->dropColumn(['token', 'expires_at', 'essay_scores', 'graded_at']);
        });
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('consented_at');
        });
    }
};
