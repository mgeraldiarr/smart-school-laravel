<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'nuptk' => ['required', 'regex:/^\d+$/', 'max:25', Rule::unique('teachers', 'nuptk')->ignore($teacher)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher?->user_id)],
            'phone_number' => ['nullable', 'regex:/^[0-9+\-\s]+$/', 'max:20'],
            'is_active' => ['boolean'],
            'reset_password' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nuptk' => 'NUPTK',
            'full_name' => 'nama guru',
            'email' => 'email',
            'phone_number' => 'nomor HP',
        ];
    }

    public function messages(): array
    {
        return [
            'nuptk.regex' => 'NUPTK hanya boleh berisi angka.',
            'phone_number.regex' => 'Nomor HP hanya boleh berisi angka, spasi, +, atau -.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'reset_password' => $this->boolean('reset_password'),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
