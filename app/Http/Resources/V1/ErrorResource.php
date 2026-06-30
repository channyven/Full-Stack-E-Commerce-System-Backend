<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    public static $wrap = 'error';

    public function toArray($request): array
    {
        return [
            'message' => $this->message,
            'status' => $this->status,
        ];
    }
}
