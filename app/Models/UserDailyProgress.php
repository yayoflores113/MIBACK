<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDailyProgress extends Model
{
    protected $table = 'user_daily_progress';
    
    protected $fillable = [
        'user_id',
        'exercise_id',
        'completed_date',
        'completed_at',
        'is_correct',
        'attempts',
        'user_answer',
        'time_spent_seconds',
        'points_earned'
    ];

    protected $casts = [
        'completed_date' => 'date',
        'completed_at' => 'datetime',
        'is_correct' => 'boolean'
    ];
}