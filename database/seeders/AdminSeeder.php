<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('salat.admin.email');
        $password = config('salat.admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum diisi di .env, akun admin tidak dibuat.');

            return;
        }

        if (app()->environment('production') && $password === 'ganti-password-ini') {
            $this->command?->error('Ganti ADMIN_PASSWORD di .env sebelum menjalankan seeder di produksi.');

            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = config('salat.admin.nama') ?: 'Admin';
        $user->password = $password;
        $user->is_admin = true; // is_admin sengaja tidak fillable, jadi tidak bisa diisi lewat formulir
        $user->save();

        $this->command?->info("Akun admin siap: {$email}");
    }
}
