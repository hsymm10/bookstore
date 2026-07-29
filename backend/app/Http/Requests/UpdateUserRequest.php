<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userParam = $this->route('user');
        $userId = $userParam instanceof \App\Models\User ? $userParam->id : ($userParam ?? 'NULL');

        return [
            'nama'     => ['sometimes', 'string', 'max:100'],
            'username' => ['sometimes', 'string', 'max:50', "unique:users,username,{$userId}"],
            'email'    => ['sometimes', 'email', 'max:100', "unique:users,email,{$userId}"],
            'no_hp'    => ['sometimes', 'string', 'max:20', "unique:users,no_hp,{$userId}"],
            'role'     => ['sometimes', 'in:admin,user'],
            'password' => ['sometimes', 'string', 'min:8'],
            'alamat'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah digunakan.',
            'email.unique'    => 'Email sudah terdaftar.',
            'no_hp.unique'    => 'Nomor telepon sudah digunakan oleh pengguna lain.',
            'role.in'         => 'Role harus admin atau user.',
            'password.min'    => 'Password minimal 8 karakter.',
        ];
    }
}
