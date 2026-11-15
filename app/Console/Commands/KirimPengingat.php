<?php

namespace App\Console\Commands;

use App\Models\LanggananPush;
use App\Services\JadwalSalat;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class KirimPengingat extends Command
{
    protected $signature = 'salat:kirim {--uji : Kirim notifikasi percobaan ke semua langganan sekarang}';

    protected $description = 'Kirim push notification untuk waktu salat yang tiba (jalankan tiap menit lewat scheduler)';

    private const WAKTU = ['Subuh', 'Zuhur', 'Asar', 'Magrib', 'Isya'];

    public function handle(JadwalSalat $layanan): int
    {
        if (! class_exists(\Minishlink\WebPush\WebPush::class)) {
            $this->error('Paket belum terpasang. Jalankan: composer require minishlink/web-push');

            return self::FAILURE;
        }

        if (! config('salat.vapid_public') || ! config('salat.vapid_private')) {
            $this->error('Kunci VAPID belum diisi. Jalankan: php artisan salat:vapid --simpan');

            return self::FAILURE;
        }

        $antrean = []; // [id => [langganan, payload]]

        if ($this->option('uji')) {
            foreach (LanggananPush::all() as $l) {
                $antrean[$l->id] = [$l, ['judul' => 'Uji notifikasi', 'isi' => 'Push notification berfungsi.', 'tag' => 'uji']];
            }
        } else {
            $this->kumpulkan($layanan, $antrean);
        }

        if (! $antrean) {
            return self::SUCCESS;
        }

        $this->kirim($antrean);

        return self::SUCCESS;
    }

    /** Pilih langganan yang waktu salatnya baru saja tiba (toleransi 2 menit, tanpa kirim ganda). */
    private function kumpulkan(JadwalSalat $layanan, array &$antrean): void
    {
        $kelompok = LanggananPush::all()->groupBy(fn ($l) => round((float) $l->lat, 3).'|'.round((float) $l->lon, 3).'|'.$l->tz);

        foreach ($kelompok as $daftar) {
            $first = $daftar->first();
            $jadwal = $layanan->ambil((float) $first->lat, (float) $first->lon, $first->tz);
            if (! $jadwal) {
                continue;
            }

            $now = Carbon::now($first->tz);
            $jumat = $now->dayOfWeek === Carbon::FRIDAY;

            foreach (self::WAKTU as $waktu) {
                $jam = Carbon::createFromFormat('Y-m-d H:i', $jadwal['tanggal'].' '.$jadwal['jadwal'][$waktu], $first->tz);
                $telat = $jam->diffInMinutes($now, false);
                if ($telat < 0 || $telat > 2) {
                    continue;
                }

                $label = ($waktu === 'Zuhur' && $jumat) ? 'Jumat' : $waktu;

                foreach ($daftar as $l) {
                    $kunci = "push:{$l->id}:{$jadwal['tanggal']}:{$waktu}";
                    if (! Cache::add($kunci, 1, 86400)) {
                        continue;
                    }
                    $antrean[$l->id] = [$l, [
                        'judul' => 'Waktu '.$label.($l->nama ? ' '.$l->nama : ''),
                        'isi' => 'Saatnya salat '.$label.' ('.$jadwal['jadwal'][$waktu].').',
                        'tag' => 'salat-'.$waktu,
                    ]];
                }
            }
        }
    }

    private function kirim(array $antrean): void
    {
        $webPush = new \Minishlink\WebPush\WebPush(['VAPID' => [
            'subject' => config('salat.vapid_subject'),
            'publicKey' => config('salat.vapid_public'),
            'privateKey' => config('salat.vapid_private'),
        ]]);

        $peta = [];
        foreach ($antrean as $id => [$l, $payload]) {
            $peta[$l->endpoint] = $l;
            $webPush->queueNotification(
                \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $l->endpoint,
                    'publicKey' => $l->p256dh,
                    'authToken' => $l->auth,
                ]),
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            );
        }

        $ok = 0;
        $hapus = 0;
        foreach ($webPush->flush() as $laporan) {
            if ($laporan->isSuccess()) {
                $ok++;
            } elseif ($laporan->isSubscriptionExpired()) {
                // Pengguna mencabut izin / langganan kedaluwarsa
                LanggananPush::where('endpoint_hash', hash('sha256', $laporan->getEndpoint()))->delete();
                $hapus++;
            } else {
                $this->warn('Gagal kirim: '.$laporan->getReason());
            }
        }

        $this->info("Terkirim: {$ok}, langganan dihapus: {$hapus}");
    }
}
