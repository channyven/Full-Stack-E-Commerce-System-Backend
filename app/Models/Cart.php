<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends BaseModel
{
    protected $fillable = ['user_id', 'device_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function itemCount(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function subtotal(): string
    {
        return number_format(
            $this->items->sum(fn (CartItem $item) => (float) $item->unit_price * (int) $item->quantity),
            2,
            '.',
            ''
        );
    }
}
