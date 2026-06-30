<?php

namespace App\Http\Resources\V1;

class ProductImageResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'path' => $this->path,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
        ];
    }
}
