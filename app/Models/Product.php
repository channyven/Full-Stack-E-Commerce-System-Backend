<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\ScopedQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use ScopedQuery, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
        'category_id',
        'is_featured',
        'is_active',
        'status',
        'slug',
        'sku',
        'images',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'status' => ProductStatus::class,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function currentPrice(): string
    {
        return (string) ($this->sale_price !== null ? $this->sale_price : $this->price);
    }

    public function isInStock(int $quantity = 1): bool
    {
        return (int) $this->stock_quantity >= $quantity;
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $needle = trim($term);

        return $query->where(function (Builder $inner) use ($needle) {
            $inner->where('name', 'like', "%{$needle}%")
                ->orWhere('sku', 'like', "%{$needle}%")
                ->orWhere('description', 'like', "%{$needle}%");
        });
    }
}
