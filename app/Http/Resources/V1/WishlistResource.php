<?php

namespace App\Http\Resources\V1;

class WishlistResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => $this->relationLoaded('items')
                ? WishlistItemResource::collection($this->items)
                : [],
        ];
    }
}
