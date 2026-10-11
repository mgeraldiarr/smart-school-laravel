<?php

/*
 * Pesan validasi Bahasa Indonesia. Aturan yang tidak ada di sini jatuh ke
 * bahasa cadangan (APP_FALLBACK_LOCALE).
 */
return [
    'accepted' => ':Attribute harus disetujui.',
    'after' => ':Attribute harus tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'array' => ':Attribute harus berupa daftar.',
    'before' => ':Attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':Attribute harus antara :min dan :max.',
        'file' => 'Ukuran :attribute harus antara :min dan :max kilobyte.',
        'string' => ':Attribute harus antara :min dan :max karakter.',
        'array' => ':Attribute harus berisi :min sampai :max item.',
    ],
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => ':Attribute harus berformat :format.',
    'decimal' => ':Attribute harus memiliki :decimal angka desimal.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus :digits digit.',
    'digits_between' => ':Attribute harus antara :min dan :max digit.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak ditemukan.',
    'extensions' => ':Attribute harus berformat: :values.',
    'file' => ':Attribute harus berupa berkas.',
    'filled' => ':Attribute wajib diisi.',
    'gt' => [
        'numeric' => ':Attribute harus lebih besar dari :value.',
        'string' => ':Attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':Attribute minimal :value karakter.',
    ],
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus bilangan bulat.',
    'lt' => [
        'numeric' => ':Attribute harus lebih kecil dari :value.',
        'string' => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'numeric' => ':Attribute harus lebih kecil dari atau sama dengan :value.',
        'string' => ':Attribute maksimal :value karakter.',
    ],
    'max' => [
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobyte.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
        'array' => ':Attribute tidak boleh lebih dari :max item.',
    ],
    'mimes' => ':Attribute harus berformat: :values.',
    'mimetypes' => ':Attribute harus berformat: :values.',
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'string' => ':Attribute minimal :min karakter.',
        'array' => ':Attribute minimal berisi :min item.',
    ],
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'present' => ':Attribute wajib ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_if' => ':Attribute wajib diisi bila :other bernilai :value.',
    'required_unless' => ':Attribute wajib diisi kecuali :other bernilai :values.',
    'required_with' => ':Attribute wajib diisi bila :values diisi.',
    'required_with_all' => ':Attribute wajib diisi bila :values diisi.',
    'required_without' => ':Attribute wajib diisi bila :values kosong.',
    'required_without_all' => ':Attribute wajib diisi bila :values semuanya kosong.',
    'same' => ':Attribute dan :other harus sama.',
    'size' => [
        'numeric' => ':Attribute harus :size.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'string' => ':Attribute harus :size karakter.',
        'array' => ':Attribute harus berisi :size item.',
    ],
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah dipakai.',
    'uploaded' => ':Attribute gagal diunggah.',
    'url' => ':Attribute harus berupa URL yang valid.',

    'custom' => [],

    'attributes' => [
        'email' => 'email',
        'password' => 'kata sandi',
        'file' => 'berkas',
    ],
];
