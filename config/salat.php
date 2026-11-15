<?php

/*
 | Pengaturan Pengingat Salat.
 | Tambah kota baru cukup dengan menambah satu baris di array 'kota'.
 | tz: Asia/Jakarta (WIB), Asia/Makassar (WITA), Asia/Jayapura (WIT).
 */
return [
    'metode' => (int) env('SALAT_METODE', 20), // 20 = Kemenag RI (Aladhan)

    'bawaan' => 'Sigli',

    'admin' => [
        'nama' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Web Push (VAPID). Isi otomatis dengan: php artisan salat:vapid --simpan
    'vapid_subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
    'vapid_public' => env('VAPID_PUBLIC_KEY'),
    'vapid_private' => env('VAPID_PRIVATE_KEY'),

    'kota' => [
        ['nama' => 'Sigli',        'lat' => 5.3871,  'lon' => 95.9604,  'tz' => 'Asia/Jakarta'],
        ['nama' => 'Banda Aceh',   'lat' => 5.5483,  'lon' => 95.3238,  'tz' => 'Asia/Jakarta'],
        ['nama' => 'Medan',        'lat' => 3.5952,  'lon' => 98.6722,  'tz' => 'Asia/Jakarta'],
        ['nama' => 'Jakarta',      'lat' => -6.2088, 'lon' => 106.8456, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Bandung',      'lat' => -6.9175, 'lon' => 107.6191, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Surabaya',     'lat' => -7.2575, 'lon' => 112.7521, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Makassar',     'lat' => -5.1477, 'lon' => 119.4327, 'tz' => 'Asia/Makassar'],
        ['nama' => 'Jayapura',     'lat' => -2.5337, 'lon' => 140.7181, 'tz' => 'Asia/Jayapura'],
        ['nama' => 'Bireuen',      'lat' => 5.2030,  'lon' => 96.7010,  'tz' => 'Asia/Jakarta'],
        ['nama' => 'Lhokseumawe',  'lat' => 5.1801,  'lon' => 97.1507,  'tz' => 'Asia/Jakarta'],
        ['nama' => 'Pekanbaru',    'lat' => 0.5071,  'lon' => 101.4478, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Padang',       'lat' => -0.9471, 'lon' => 100.4172, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Palembang',    'lat' => -2.9761, 'lon' => 104.7754, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Semarang',     'lat' => -6.9667, 'lon' => 110.4167, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Yogyakarta',   'lat' => -7.7956, 'lon' => 110.3695, 'tz' => 'Asia/Jakarta'],
        ['nama' => 'Denpasar',     'lat' => -8.6705, 'lon' => 115.2126, 'tz' => 'Asia/Makassar'],
        ['nama' => 'Balikpapan',   'lat' => -1.2379, 'lon' => 116.8529, 'tz' => 'Asia/Makassar'],
        ['nama' => 'Manado',       'lat' => 1.4748,  'lon' => 124.8421, 'tz' => 'Asia/Makassar'],
    ],
];
