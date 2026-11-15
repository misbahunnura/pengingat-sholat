<?php

namespace Database\Seeders;

use App\Models\Dzikir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DzikirSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya mengisi saat tabel masih kosong, supaya perubahan dari halaman admin tidak tertimpa.
        if (Dzikir::exists()) {
            return;
        }

        $data = json_decode(file_get_contents(resource_path('data/dzikir.json')), true) ?? [];

        foreach ($data as $d) {
            Dzikir::create(Arr::only($d, ['waktu', 'urutan', 'judul', 'arab', 'latin', 'arti', 'jumlah', 'sumber']));
        }
    }
}
