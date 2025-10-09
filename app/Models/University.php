<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class University extends Model
{
    use HasFactory, SoftDeletes;

    // Campos asignables en masa (igual que tenías)
    protected $fillable = [
        'name',
        'acronym',
        'slug',
        'country',
        'state',
        'city',
        'website',
        'description',
        'logo_url',
        'established_year',
        'orden',
    ];

    protected $casts = [
        'established_year' => 'integer',
        'orden'            => 'integer',
    ];

    /** Relaciones (igual que tenías) */
    public function careers()
    {
        return $this->hasMany(Career::class);
    }

    /** Scope útil (igual que tenías) */
    public function scopeInCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Hook para aplicar campos del Request antes de guardar.
     * No cambia tu controller: al llamar ->save(), aquí se copian
     * los campos permitidos del request si vienen.
     */
    protected static function booted(): void
    {
        static::saving(function (self $u) {
            $req = request();
            if (!$req) return;

            // Acepta alias 'nombre' -> 'name' (tu flujo lo usa)
            if ($req->filled('nombre') && !$req->filled('name')) {
                $u->name = $req->input('nombre');
            }

            // Solo estos campos serán “actualizables” vía API
            $updatable = [
                'name',
                'acronym',
                'country',
                'state',
                'city',
                'website',
                'description',
                'established_year',
                'orden',
                'slug',
            ];

            foreach ($updatable as $field) {
                if ($req->has($field)) {
                    $u->{$field} = $req->input($field);
                }
            }

            // Si cambió el name y no envías slug explícito, regenéralo
            if ($u->isDirty('name') && !$req->has('slug')) {
                $u->slug = Str::slug($u->name);
            }
        });
    }
}
