<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VocationalTrait extends Model
{
    use HasFactory;

    // La tabla en BD se llama 'traits'
    protected $table = 'traits';

    protected $fillable = ['code', 'name', 'description'];

    /** Relaciones */
    public function answerOptions()
    {
        return $this->hasMany(AnswerOption::class, 'trait_id');
    }

    public function attemptScores()
    {
        return $this->hasMany(AttemptTraitScore::class, 'trait_id');
    }
}
