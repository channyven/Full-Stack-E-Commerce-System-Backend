<?php

namespace App\Http\Resources\V1;

class CartItemResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal(),
            'product' => ProductResource::make($this->whenLoaded('product') ?? $this->product),
        ];
    }
}
