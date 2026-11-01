<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $assessment = Assessment::create([
            'title' => 'Tes Dasar Logika & Pengetahuan Umum',
            'type' => 'psychometric',
            'description' => 'Tes singkat untuk menilai kemampuan berpikir logis dasar kandidat.',
            'duration_minutes' => 20,
            'passing_score' => 60,
        ]);

        $assessment->questions()->createMany([
            [
                'question' => 'Jika semua kucing adalah hewan, dan Milo adalah kucing, maka...',
                'question_type' => 'multiple_choice',
                'options' => ['A' => 'Milo adalah hewan', 'B' => 'Milo bukan hewan', 'C' => 'Semua hewan adalah kucing', 'D' => 'Tidak dapat disimpulkan'],
                'correct_option' => 'A',
                'points' => 1,
                'order' => 1,
            ],
            [
                'question' => 'Lanjutkan pola angka: 2, 4, 8, 16, ...',
                'question_type' => 'multiple_choice',
                'options' => ['A' => '18', 'B' => '24', 'C' => '32', 'D' => '20'],
                'correct_option' => 'C',
                'points' => 1,
                'order' => 2,
            ],
            [
                'question' => 'Ceritakan singkat pengalaman kerja tim yang paling berkesan bagimu.',
                'question_type' => 'essay',
                'points' => 2,
                'order' => 3,
            ],
        ]);
    }
}
