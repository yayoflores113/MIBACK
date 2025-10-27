<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'duration',
        'level',
        'objectives',
        'requirements',
        'is_active',
        'order'
    ];

    protected $casts = [
        'objectives' => 'array',
        'requirements' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relación muchos a muchos con Courses
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'learning_path_course')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('learning_path_course.order');
    }

    /**
     * Accessor para contar cursos
     */
    public function getCoursesCountAttribute()
    {
        return $this->courses()->count();
    }

    /**
     * Boot method para generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($learningPath) {
            if (empty($learningPath->slug)) {
                $learningPath->slug = Str::slug($learningPath->title);
            }
        });

        static::updating(function ($learningPath) {
            if ($learningPath->isDirty('title') && empty($learningPath->slug)) {
                $learningPath->slug = Str::slug($learningPath->title);
            }
        });
    }

    /**
     * Scope para obtener solo rutas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por campo 'order'
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
