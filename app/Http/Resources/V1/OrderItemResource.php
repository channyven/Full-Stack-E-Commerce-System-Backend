<?php

namespace App\Http\Resources\V1;

class OrderItemResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'subtotal' => $this->subtotal(),
            'product' => $this->relationLoaded('product')
                ? ProductResource::make($this->product)
                : null,
        ];
    }
}
