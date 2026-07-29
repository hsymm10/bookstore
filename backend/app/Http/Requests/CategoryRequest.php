<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ignore current ID saat update agar unique tetap bisa update nama yang sama
        $categoryId = $this->route('category')?->id ?? 'NULL';

        return [
            'nama' => [
                'required',
                'string',
                'max:100',
                "unique:categories,nama,{$categoryId}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique'   => 'Nama kategori sudah terdaftar.',
            'nama.max'      => 'Nama kategori maksimal 100 karakter.',
        ];
    }
}
