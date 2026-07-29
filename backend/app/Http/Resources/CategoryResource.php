<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nama'        => $this->nama,
            'slug'        => $this->slug,
            'total_buku'  => $this->whenCounted('books'),
            'created_at'  => $this->created_at?->format('d M Y'),
        ];
    }
}
