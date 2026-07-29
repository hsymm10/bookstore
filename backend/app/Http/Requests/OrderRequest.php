<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'           => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer', 'exists:books,id'],
            'items.*.qty'     => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'           => 'Keranjang belanja tidak boleh kosong.',
            'items.array'              => 'Format keranjang tidak valid.',
            'items.min'                => 'Minimal 1 item di keranjang.',
            'items.*.book_id.required' => 'ID buku wajib ada.',
            'items.*.book_id.exists'   => 'Buku tidak ditemukan.',
            'items.*.qty.required'     => 'Jumlah buku wajib diisi.',
            'items.*.qty.min'          => 'Jumlah buku minimal 1.',
        ];
    }
}
