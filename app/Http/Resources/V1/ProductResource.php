<?php

namespace App\Http\Resources\V1;

class ProductResource extends JsonApiResource
{
    public function toArray($request): array
    {
        $primaryImage = null;
        if ($this->relationLoaded('productImages') && $this->productImages->isNotEmpty()) {
            $primaryImage = ProductImageResource::make($this->productImages->first());
        } elseif (is_array($this->images) && count($this->images) > 0) {
            $primaryImage = $this->images[0];
        }

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'status' => $this->status?->value ?? $this->status,
            'images' => $this->images,
            'product_images' => $this->relationLoaded('productImages')
                ? ProductImageResource::collection($this->productImages)
                : [],
            'category' => $this->relationLoaded('category')
                ? CategoryResource::make($this->category)
                : null,
            'primary_image' => $primaryImage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
