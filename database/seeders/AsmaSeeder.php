<?php

namespace Database\Seeders;

use App\Models\Asma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AsmaSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya mengisi saat tabel masih kosong, supaya perubahan dari halaman admin tidak tertimpa.
        if (Asma::exists()) {
            return;
        }

        $data = json_decode(file_get_contents(resource_path('data/asma.json')), true) ?? [];

        foreach ($data as $d) {
            Asma::create(Arr::only($d, ['nomor', 'arab', 'latin', 'arti']));
        }
    }
}
