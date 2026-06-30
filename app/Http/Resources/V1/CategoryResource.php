<?php

namespace App\Http\Resources\V1;

class CategoryResource extends JsonApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'children' => $this->relationLoaded('children')
                ? CategoryResource::collection($this->children)
                : [],
        ];
    }
}
