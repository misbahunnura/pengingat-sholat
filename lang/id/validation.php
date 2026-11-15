<?php

return [
    'required' => ':attribute wajib diisi.',
    'present' => ':attribute harus ada.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'string' => ':attribute harus berupa teks.',
    'integer' => ':attribute harus berupa angka bulat.',
    'numeric' => ':attribute harus berupa angka.',
    'boolean' => ':attribute harus benar atau salah.',
    'array' => ':attribute harus berupa daftar.',
    'unique' => ':attribute sudah dipakai.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'in' => ':attribute tidak valid.',
    'date_format' => 'Format :attribute tidak valid.',
    'between' => [
        'numeric' => ':attribute harus di antara :min dan :max.',
    ],
    'max' => [
        'numeric' => ':attribute maksimal :max.',
        'string' => ':attribute maksimal :max karakter.',
        'array' => ':attribute maksimal :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'password' => [
        'min' => ':attribute minimal :min karakter.',
    ],
    'attributes' => [
        'name' => 'Nama',
        'email' => 'Email',
        'password' => 'Kata sandi',
        'judul' => 'Judul',
        'kategori' => 'Kategori',
        'arab' => 'Teks Arab',
        'latin' => 'Latin',
        'arti' => 'Arti',
        'lat' => 'Lintang',
        'lon' => 'Bujur',
        'tz' => 'Zona waktu',
        'waktu' => 'Waktu',
        'urutan' => 'Urutan',
        'jumlah' => 'Jumlah bacaan',
        'sumber' => 'Sumber',
        'nomor' => 'Nomor',
    ],
];
