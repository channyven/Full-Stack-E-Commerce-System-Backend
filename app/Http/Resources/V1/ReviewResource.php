<?php

namespace App\Http\Resources\V1;

class ReviewResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'rating' => $this->rating,
            'title' => $this->title,
            'content' => $this->content,
            'verified_purchase' => $this->verified_purchase,
            'status' => $this->status?->value ?? $this->status,
            'created_at' => $this->created_at,
            'user' => $this->relationLoaded('user')
                ? UserResource::make($this->user)
                : null,
            'product' => $this->relationLoaded('product')
                ? ProductResource::make($this->product)
                : null,
        ];
    }
}
