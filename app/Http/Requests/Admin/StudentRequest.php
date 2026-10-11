<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creating = ! $this->route('student');

        return [
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($this->route('student'))],
            'full_name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before:today'],
            'is_active' => ['boolean'],
            // Data wali wajib saat menambah siswa, opsional saat mengubah.
            'parent_name' => [$creating ? 'required' : 'nullable', 'required_with:parent_email', 'string', 'max:255'],
            'parent_email' => [$creating ? 'required' : 'nullable', 'required_with:parent_name', 'email', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nis' => 'NIS',
            'full_name' => 'nama siswa',
            'class_name' => 'kelas',
            'birth_date' => 'tanggal lahir',
            'parent_name' => 'nama wali',
            'parent_email' => 'email wali',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'parent_email' => $this->filled('parent_email') ? strtolower(trim($this->input('parent_email'))) : null,
        ]);
    }
}
