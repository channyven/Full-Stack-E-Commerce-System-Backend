<?php

namespace App\Http\Resources\V1;

class CartResource extends JsonApiResource
{
    public function toArray($request): array
    {
        $items = $this->relationLoaded('items') ? $this->items : collect();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'device_token' => $this->device_token,
            'items' => CartItemResource::collection($items),
            'item_count' => $this->itemCount(),
            'subtotal' => $this->subtotal(),
        ];
    }
}
