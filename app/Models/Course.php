<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_id',
        'title',
        'slug',
        'provider',
        'url',
        'topic',
        'level',
        'difficulty',
        'hours',
        'is_premium',
        'is_free',
        'price_cents',
        'rating_avg',
        'rating_count',
        'popularity_score',
        'published_at',
        'card_image_url',
        'description',
        // (opcional) 'orden' si lo vas a actualizar vía API
    ];

    protected $casts = [
        'hours'            => 'integer',
        'is_premium'       => 'boolean',
        'is_free'          => 'boolean',
        'price_cents'      => 'integer',
        'rating_avg'       => 'float',
        'rating_count'     => 'integer',
        'popularity_score' => 'integer',
        'published_at'     => 'date',
        'orden' => 'integer',
    ];

    public function career()
    {
        return $this->belongsTo(Career::class);
    }
}
