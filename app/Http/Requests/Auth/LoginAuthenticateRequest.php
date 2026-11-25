<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginAuthenticateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'remember'         => ['nullable', 'boolean'],
            'face_embedding'   => ['nullable'],
            'is_face_verified' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'  => 'Silakan pilih nama Anda.',
            'user_id.exists'    => 'Pengguna yang dipilih tidak valid.',
        ];
    }
}
