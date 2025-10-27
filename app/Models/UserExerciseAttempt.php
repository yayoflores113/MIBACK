<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserExerciseAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'exercise_id',
        'user_answer',
        'is_correct',
        'points_earned',
        'time_spent',
        'completed_date',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'completed_date' => 'date',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(DailyExercise::class, 'exercise_id');
    }

    // Scopes
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('completed_date', $date);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}