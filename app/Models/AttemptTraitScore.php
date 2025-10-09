<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttemptTraitScore extends Model
{
    use HasFactory;

    protected $fillable = ['attempt_id', 'trait_id', 'score', 'normalized_score'];

    protected $casts = [
        'score' => 'integer',
        'normalized_score' => 'float',
    ];

    /** Relaciones */
    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function trait()
    {
        return $this->belongsTo(VocationalTrait::class, 'trait_id');
    }
}
