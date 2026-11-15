<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class JadwalSalat
{
    private const BULAN_HIJRIAH = [
        1 => 'Muharram', 2 => 'Safar', 3 => 'Rabiulawal', 4 => 'Rabiulakhir',
        5 => 'Jumadilawal', 6 => 'Jumadilakhir', 7 => 'Rajab', 8 => 'Syaban',
        9 => 'Ramadan', 10 => 'Syawal', 11 => 'Zulkaidah', 12 => 'Zulhijah',
    ];

    /**
     * Jadwal salat hari ini (menurut zona waktu lokasi), di-cache sampai akhir hari.
     */
    public function ambil(float $lat, float $lon, string $tz): ?array
    {
        $lat = round($lat, 3);
        $lon = round($lon, 3);

        $now = Carbon::now($tz);
        $key = "jadwal:{$lat}:{$lon}:{$tz}:".$now->format('Y-m-d');

        $data = Cache::get($key);
        if ($data) {
            return $data;
        }

        // Jangan menekan API terus-menerus kalau baru saja gagal
        if (Cache::has($key.':gagal')) {
            return null;
        }

        $data = $this->unduh($lat, $lon, $tz, $now);

        if ($data) {
            Cache::put($key, $data, $now->copy()->endOfDay()->addMinutes(5));
        } else {
            Cache::put($key.':gagal', 1, 20);
        }

        return $data;
    }

    private function unduh(float $lat, float $lon, string $tz, Carbon $now): ?array
    {
        try {
            // Sambungan ke api.aladhan.com kadang lambat (terutama dari jaringan seluler/rumah tertentu)
            $res = Http::connectTimeout(8)
                ->timeout(12)
                ->retry(1, 500)
                ->acceptJson()
                ->get('https://api.aladhan.com/v1/timings/'.$now->format('d-m-Y'), [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'method' => config('salat.metode'),
                    'timezonestring' => $tz,
                ]);

            if (! $res->ok() || (int) $res->json('code') !== 200) {
                return null;
            }

            $t = $res->json('data.timings');
            $h = $res->json('data.date.hijri');

            $jam = function ($s) {
                return preg_match('/\d{1,2}:\d{2}/', (string) $s, $m)
                    ? str_pad($m[0], 5, '0', STR_PAD_LEFT)
                    : null;
            };

            $jadwal = [
                'Imsak' => $jam($t['Imsak'] ?? null),
                'Subuh' => $jam($t['Fajr'] ?? null),
                'Terbit' => $jam($t['Sunrise'] ?? null),
                'Zuhur' => $jam($t['Dhuhr'] ?? null),
                'Asar' => $jam($t['Asr'] ?? null),
                'Magrib' => $jam($t['Maghrib'] ?? null),
                'Isya' => $jam($t['Isha'] ?? null),
            ];

            if (in_array(null, $jadwal, true)) {
                return null;
            }

            $bulan = self::BULAN_HIJRIAH[(int) ($h['month']['number'] ?? 0)] ?? ($h['month']['en'] ?? '');

            return [
                'tanggal' => $now->format('Y-m-d'),
                'tz' => $tz,
                'hijri' => trim(((int) ($h['day'] ?? 0)).' '.$bulan.' '.($h['year'] ?? '').' H'),
                'jadwal' => $jadwal,
            ];
        } catch (Throwable $e) {
            Log::warning('Gagal mengambil jadwal salat: '.$e->getMessage());

            return null;
        }
    }
}
