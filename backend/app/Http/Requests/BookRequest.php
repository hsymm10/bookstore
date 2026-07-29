<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book')?->id ?? 'NULL';
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'nama_buku'      => [
                'required',
                'string',
                'max:255',
                "unique:books,nama_buku,{$bookId}",
            ],
            'tanggal_terbit' => ['nullable', 'date'],
            'tahun_terbit'   => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'stok'           => ['required', 'integer', 'min:0'],
            'harga_modal'    => ['required', 'numeric', 'min:0'],
            'harga_jual'     => ['required', 'numeric', 'min:0', 'gte:harga_modal'],
            'deskripsi'      => ['nullable', 'string'],
            'gambar'         => [
                $isUpdate ? 'nullable' : 'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'  => 'Kategori wajib dipilih.',
            'category_id.exists'    => 'Kategori tidak valid.',
            'nama_buku.required'    => 'Nama buku wajib diisi.',
            'nama_buku.unique'      => 'Nama buku sudah terdaftar.',
            'stok.required'         => 'Stok wajib diisi.',
            'stok.min'              => 'Stok tidak boleh negatif.',
            'harga_modal.required'  => 'Harga modal wajib diisi.',
            'harga_jual.required'   => 'Harga jual wajib diisi.',
            'harga_jual.gte'        => 'Harga jual harus lebih besar atau sama dengan harga modal.',
            'gambar.image'          => 'File harus berupa gambar.',
            'gambar.mimes'          => 'Format gambar harus jpeg, jpg, png, atau webp.',
            'gambar.max'            => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
