<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyExercise extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'content',
        'solution',
        'difficulty',
        'points',
        'is_active',
        'available_date',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'available_date' => 'date',
    ];

    // Relaciones
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(UserExerciseAttempt::class, 'exercise_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForToday($query)
    {
        $today = now()->toDateString();
        
        return $query->active()
            ->where(function($q) use ($today) {
                $q->where('available_date', $today)
                  ->orWhereNull('available_date');
            });
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    // Métodos auxiliares
    public function hasBeenCompletedByUser($userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('completed_date', now()->toDateString())
            ->exists();
    }

    public function validateAnswer($userAnswer): bool
    {
        // Normalizar respuestas para comparación
        $normalizedUserAnswer = strtolower(trim($userAnswer));
        $normalizedSolution = strtolower(trim($this->solution));

        // Comparación directa
        if ($normalizedUserAnswer === $normalizedSolution) {
            return true;
        }

        // Para respuestas de código, podrías implementar lógica más compleja
        // Por ejemplo, ejecutar el código o comparar outputs
        
        return false;
    }
}