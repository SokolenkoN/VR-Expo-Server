<?php

namespace App\Http\Resources\File;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            "size" => $this->resource->size,
            'microtime_name' => $this->microtime_name,
            'link' => $this->link,
            "updated_at" => $this->resource->updated_at,
            "created_at" => $this->resource->created_at,

        ];
    }
}
