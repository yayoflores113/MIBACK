<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_session_id',
        'amount_cents',
        'currency',
        'status',
        'metadata',
        'product_name',
        'stripe_payment_intent',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];
}
