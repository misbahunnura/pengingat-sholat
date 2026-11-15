<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DoaSeeder::class,
            AsmaSeeder::class,
            DzikirSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
