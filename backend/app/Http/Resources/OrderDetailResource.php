<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'book'     => new BookResource($this->whenLoaded('book')),
            'qty'      => $this->qty,
            'harga'    => (float) $this->harga,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
