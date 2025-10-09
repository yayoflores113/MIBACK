<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_cents',
        'currency',
        'interval',            // month|year
        'features',            // JSON (array)
        'stripe_product_id',
        'stripe_price_id',
        'is_active',
        'trial_days',
        'sort_order',

        //mejoras para la UI
        'subtitle',            // "Para ti", "Individual", etc.
        'cta_type',            // trial|subscribe|contact
        'cta_label',           // "Probar gratis", "Suscribirme", "Contactar"
        'is_featured',         // destacar en la grilla de planes
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'features'    => 'array',
        'is_active'   => 'boolean',
        'trial_days'  => 'integer',
        'sort_order'  => 'integer',
        'is_featured' => 'boolean',
    ];

    /** Relaciones */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /** Scopes */
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** (Opcional) Accesor para mostrar precio formateado en MXN/USD, etc. */
    public function getPriceFormattedAttribute(): ?string
    {
        if ($this->price_cents === null) return null;
        return '$' . number_format($this->price_cents / 100, 2) . ' ' . $this->currency;
    }
}
