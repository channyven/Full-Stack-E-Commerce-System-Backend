<?php

namespace App\Models;

class WishlistItem extends BaseModel
{
    protected $fillable = ['wishlist_id', 'product_id'];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
