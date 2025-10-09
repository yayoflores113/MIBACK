<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['attempt_id', 'question_id', 'answer_option_id', 'answer_value', 'score'];

    protected $casts = [
        'answer_value' => 'integer',
        'score' => 'integer',
    ];

    /** Relaciones */
    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answerOption()
    {
        return $this->belongsTo(AnswerOption::class);
    }
}
