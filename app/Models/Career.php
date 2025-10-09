<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Career extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orden',
        'university_id',
        'name',
        'slug',
        'level',
        'levels',
        'area',
        'division',
        'duration_months',
        'duration_terms',
        'terms_unit',
        'modality',
        'description',
        'career_url',
    ];

    protected $casts = [
        'levels' => 'array',
        'duration_terms' => 'array',
        'duration_months' => 'integer',
        'orden' => 'integer',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function recommendations()
    {
        return $this->morphMany(Recommendation::class, 'recommendable');
    }
    public function scopeOrdered($q)
    {
        return $q->orderBy('orden');
    }

    protected static function booted(): void
    {
        static::saving(function (self $c) {
            $req = request();
            if (!$req) return;

            // Alias 'nombre' -> 'name'
            if ($req->filled('nombre') && !$req->filled('name')) {
                $c->name = $req->input('nombre');
            }

            // Copiar campos si vienen en el request
            $updatable = [
                'university_id',
                'name',
                'slug',
                'level',
                'levels',
                'area',
                'division',
                'duration_months',
                'duration_terms',
                'terms_unit',
                'modality',
                'description',
                'orden'
            ];
            foreach ($updatable as $field) {
                if ($req->has($field)) {
                    $c->{$field} = $req->input($field);
                }
            }

            // Auto-generar slug:
            // - Si está vacío (create) o
            // - Si cambió 'name' y NO mandaron 'slug' (update)
            //if (blank($c->slug) || ($c->isDirty('name') && !$req->has('slug'))) {
                //$c->slug = Str::slug((string) $c->name);
            //}

            
            // Forzar: el slug SIEMPRE debe corresponder al 'name'
            $c->slug = Str::slug((string) $c->name);
        });
    }
}
