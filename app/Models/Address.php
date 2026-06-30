<?php

namespace App\Models;

class Address extends BaseModel
{
    protected $fillable = [
        'user_id',
        'type',
        'full_name',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
