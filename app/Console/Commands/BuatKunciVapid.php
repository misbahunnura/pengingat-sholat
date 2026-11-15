<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BuatKunciVapid extends Command
{
    protected $signature = 'salat:vapid {--simpan : Tulis kunci ke file .env}';

    protected $description = 'Buat pasangan kunci VAPID untuk push notification';

    public function handle(): int
    {
        if (! class_exists(\Minishlink\WebPush\VAPID::class)) {
            $this->error('Paket belum terpasang. Jalankan: composer require minishlink/web-push');

            return self::FAILURE;
        }

        $k = \Minishlink\WebPush\VAPID::createVapidKeys();

        if (! $this->option('simpan')) {
            $this->line('VAPID_PUBLIC_KEY='.$k['publicKey']);
            $this->line('VAPID_PRIVATE_KEY='.$k['privateKey']);
            $this->comment('Tambahkan --simpan untuk menulis otomatis ke .env');

            return self::SUCCESS;
        }

        $env = base_path('.env');
        $isi = is_file($env) ? file_get_contents($env) : '';

        if (preg_match('/^VAPID_PRIVATE_KEY=.+$/m', $isi) && ! $this->confirm('Kunci VAPID sudah ada. Timpa? Langganan lama berhenti berfungsi.', false)) {
            return self::SUCCESS;
        }

        foreach (['VAPID_PUBLIC_KEY' => $k['publicKey'], 'VAPID_PRIVATE_KEY' => $k['privateKey']] as $nama => $nilai) {
            $isi = preg_match("/^{$nama}=.*$/m", $isi)
                ? preg_replace("/^{$nama}=.*$/m", "{$nama}={$nilai}", $isi)
                : rtrim($isi)."\n{$nama}={$nilai}\n";
        }
        file_put_contents($env, $isi);

        $this->info('Kunci VAPID disimpan di .env. Jalankan: php artisan config:clear');

        return self::SUCCESS;
    }
}
