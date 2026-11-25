<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudyProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('study_program')->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:study_programs,email,' . $id],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'       => 'Email sudah terdaftar untuk program studi lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
        ];
    }
}
