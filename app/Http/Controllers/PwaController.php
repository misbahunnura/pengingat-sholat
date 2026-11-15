<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $ikon = fn (string $f, int $u, string $tujuan = 'any') => [
            'src' => asset("icons/{$f}"), 'sizes' => "{$u}x{$u}", 'type' => 'image/png', 'purpose' => $tujuan,
        ];

        return response()->json([
            'id' => '/',
            'name' => config('app.name'),
            'short_name' => 'Salat',
            'description' => 'Jadwal salat, pengingat azan, Al-Quran, doa, dzikir pagi/petang, dan tasbih digital.',
            'lang' => 'id',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#000000',
            'theme_color' => '#000000',
            'icons' => [
                $ikon('icon-192.png', 192),
                $ikon('icon-512.png', 512),
                $ikon('icon-maskable-512.png', 512, 'maskable'),
            ],
        ], 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_SLASHES);
    }

    public function offline(): View
    {
        return view('offline');
    }
}
