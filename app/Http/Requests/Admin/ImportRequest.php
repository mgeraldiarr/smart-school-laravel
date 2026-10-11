<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:xlsx,csv', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Pilih berkas Excel (.xlsx) atau CSV terlebih dahulu.',
            'file.extensions' => 'Berkas harus berformat .xlsx atau .csv. Berkas .xls lama: simpan ulang sebagai .xlsx dari Excel.',
            'file.max' => 'Ukuran berkas maksimal 5 MB.',
        ];
    }
}
