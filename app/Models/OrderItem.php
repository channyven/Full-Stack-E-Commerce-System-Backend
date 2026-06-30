<?php

namespace App\Models;

class OrderItem extends BaseModel
{
    protected $fillable = ['order_id', 'product_id', 'name', 'sku', 'price', 'quantity', 'total'];

    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2', 'quantity' => 'integer'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subtotal(): string
    {
        return number_format(((float) $this->price) * ((int) $this->quantity), 2, '.', '');
    }
}
