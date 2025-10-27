<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStreak extends Model
{
    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'total_exercises_completed',
        'total_points',
        'last_completion_date',
    ];

    protected $casts = [
        'last_completion_date' => 'date',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Métodos
    public function updateStreak(): void
    {
        $today = now()->toDateString();
        $lastDate = $this->last_completion_date?->toDateString();

        if (!$lastDate || $lastDate === $today) {
            // Mismo día, no actualizar racha
            return;
        }

        $yesterday = now()->subDay()->toDateString();

        if ($lastDate === $yesterday) {
            // Día consecutivo, incrementar racha
            $this->current_streak++;
            
            if ($this->current_streak > $this->longest_streak) {
                $this->longest_streak = $this->current_streak;
            }
        } else {
            // Racha rota
            $this->current_streak = 1;
        }

        $this->last_completion_date = now();
        $this->save();
    }

    public function addPoints(int $points): void
    {
        $this->total_points += $points;
        $this->total_exercises_completed++;
        $this->save();
    }
}