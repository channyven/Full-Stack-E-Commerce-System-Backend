<?php

namespace App\Models;

class CartItem extends BaseModel
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subtotal(): string
    {
        return number_format(((float) $this->unit_price) * ((int) $this->quantity), 2, '.', '');
    }
}
