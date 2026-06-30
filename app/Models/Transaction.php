<?php

namespace App\Models;

class Transaction extends BaseModel
{
    protected $fillable = [
        'order_id',
        'user_id',
        'provider',
        'transaction_reference',
        'amount',
        'currency',
        'status',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
    ];
}
