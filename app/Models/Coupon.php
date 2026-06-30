<?php

namespace App\Models;

class Coupon extends BaseModel
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_amount',
        'starts_at',
        'expires_at',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_customer')->withTimestamps();
    }
}
