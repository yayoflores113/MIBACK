<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'text',
        'type',
        'order',
    ];

    // Relación con Test
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    // Relación con opciones
    public function answerOptions()
    {
        return $this->hasMany(AnswerOption::class);
    }
}
