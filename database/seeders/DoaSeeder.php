<?php

namespace Database\Seeders;

use App\Models\Doa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DoaSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya mengisi saat tabel masih kosong, supaya perubahan dari halaman admin tidak tertimpa.
        if (Doa::exists()) {
            return;
        }

        $data = json_decode(file_get_contents(resource_path('data/doa.json')), true) ?? [];

        foreach ($data as $d) {
            Doa::create(Arr::only($d, ['judul', 'kategori', 'arab', 'latin', 'arti']));
        }
    }
}
