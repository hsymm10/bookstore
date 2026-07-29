<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'category_id'    => $this->category_id,
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'nama_buku'      => $this->nama_buku,
            'slug'           => $this->slug,
            'tanggal_terbit' => $this->tanggal_terbit?->format('d M Y'),
            'tahun_terbit'   => $this->tahun_terbit,
            'stok'           => $this->stok,
            'harga_modal'    => (float) $this->harga_modal,
            'harga_jual'     => (float) $this->harga_jual,
            'keuntungan'     => (float) $this->keuntungan,
            'deskripsi'      => $this->deskripsi,
            'gambar'         => $this->gambar,
            'gambar_url'     => $this->gambar_url,
            'created_at'     => $this->created_at?->format('d M Y'),
        ];
    }
}
