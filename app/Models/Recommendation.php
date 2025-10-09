<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = ['attempt_id', 'recommendable_type', 'recommendable_id', 'score', 'reason'];

    protected $casts = [
        'score' => 'float',
    ];

    /** Relaciones */
    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function recommendable()
    {
        // Polimórfica: puede apuntar a Career, Course, etc.
        return $this->morphTo();
    }
}
