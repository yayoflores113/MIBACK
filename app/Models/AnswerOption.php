<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'text',
        'trait_id',
        'score',
        'order',
    ];

    // Relación con Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Relación opcional con VocationalTrait
    public function trait()
    {
        return $this->belongsTo(VocationalTrait::class, 'trait_id');
    }
}
