<?php

namespace App\Models;

class ProductImage extends BaseModel
{
    protected $fillable = ['product_id', 'path', 'alt_text', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
