<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $fillable = ['assessment_id', 'question', 'question_type', 'options', 'correct_option', 'points', 'order'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
