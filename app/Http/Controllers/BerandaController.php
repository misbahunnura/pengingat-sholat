<?php

namespace App\Http\Controllers;

use App\Models\Asma;
use App\Models\Doa;
use App\Models\Dzikir;
use App\Models\Saran;

class BerandaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('beranda', [
            'konfig' => [
                'urlJadwal' => route('api.jadwal'),
                'bawaan' => config('salat.bawaan'),
                'kota' => config('salat.kota'),
                'doa' => Doa::orderBy('id')->get(['id', 'judul', 'kategori', 'arab', 'latin', 'arti']),
                'asma' => Asma::orderBy('nomor')->get(['nomor', 'arab', 'latin', 'arti']),
                'dzikir' => Dzikir::orderBy('urutan')->orderBy('id')->get(['id', 'waktu', 'judul', 'arab', 'latin', 'arti', 'jumlah', 'sumber']),
                'saranBaru' => $user ? Saran::lencana($user) : 0,
                'vapid' => config('salat.vapid_public'),
                'user' => $user ? [
                    'nama' => $user->name,
                    'admin' => (bool) $user->is_admin,
                    'lokasi' => $user->lokasi,
                    'favorit' => $user->favorit()->pluck('doa.id')->map(fn ($i) => (int) $i)->values(),
                ] : null,
            ],
        ]);
    }
}
