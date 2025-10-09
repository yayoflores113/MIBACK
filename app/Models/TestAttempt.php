<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['test_id', 'user_id', 'started_at', 'finished_at', 'result_summary'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'result_summary' => 'array',
    ];

    /** Relaciones */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(TestAnswer::class, 'attempt_id');
    }

    public function traitScores()
    {
        return $this->hasMany(AttemptTraitScore::class, 'attempt_id');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'attempt_id');
    }
}
