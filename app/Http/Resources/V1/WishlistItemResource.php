<?php

namespace App\Http\Resources\V1;

class WishlistItemResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'wishlist_id' => $this->wishlist_id,
            'product_id' => $this->product_id,
            'product' => ProductResource::make($this->whenLoaded('product') ?? $this->product),
        ];
    }
}
