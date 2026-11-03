<?php

namespace App\Services;

class AssessmentScorer
{
    /**
     * Skor 0-100 = (poin pilihan ganda benar + nilai esai) / total poin.
     * Esai yang belum dinilai dianggap 0.
     */
    public function score($questions, array $answers, array $essayScores = []): int
    {
        $total = (float) $questions->sum('points');

        if ($total <= 0) {
            return 0;
        }

        $earned = 0.0;

        foreach ($questions as $q) {
            if ($q->question_type === 'multiple_choice') {
                if (($answers[$q->id] ?? null) === $q->correct_option) {
                    $earned += $q->points;
                }
            } else {
                $earned += (float) ($essayScores[$q->id] ?? 0);
            }
        }

        return (int) round(($earned / $total) * 100);
    }
}
