<?php

namespace App\Http\Controllers;

use App\Models\CatatanSalat;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatatanController extends Controller
{
    public const WAKTU = ['Subuh', 'Zuhur', 'Asar', 'Magrib', 'Isya'];

    /**
     * Ringkasan: waktu yang sudah ditandai pada tanggal tertentu + statistik 7 hari + rangkaian hari penuh.
     * Tanggal dikirim peramban (menurut zona waktu lokasi pengguna).
     */
    public function index(Request $request): JsonResponse
    {
        $v = $request->validate(['tanggal' => ['required', 'date_format:Y-m-d']]);

        return response()->json($this->ringkasan($request, $v['tanggal']));
    }

    public function tandai(Request $request): JsonResponse
    {
        $v = $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'waktu' => ['required', Rule::in(self::WAKTU)],
            'selesai' => ['required', 'boolean'],
        ]);

        // Zona waktu terjauh ada di UTC+14 dan UTC-12, jadi tanggal sah hanya 'kemarin' sampai 'besok' menurut UTC.
        $tgl = Carbon::createFromFormat('Y-m-d', $v['tanggal'])->startOfDay();
        $utc = Carbon::now('UTC')->startOfDay();
        if ($tgl->lt($utc->copy()->subDays(2)) || $tgl->gt($utc->copy()->addDay())) {
            return response()->json(['pesan' => 'Tanggal di luar rentang yang boleh dicatat.'], 422);
        }

        $kunci = ['user_id' => $request->user()->id, 'tanggal' => $v['tanggal'], 'waktu' => $v['waktu']];

        if ($v['selesai']) {
            CatatanSalat::firstOrCreate($kunci);
        } else {
            CatatanSalat::where($kunci)->delete();
        }

        return response()->json($this->ringkasan($request, $v['tanggal']));
    }

    private function ringkasan(Request $request, string $tanggal): array
    {
        $uid = $request->user()->id;
        $hari = Carbon::createFromFormat('Y-m-d', $tanggal)->startOfDay();

        // 30 hari terakhir untuk menghitung rangkaian dan statistik
        $dari = $hari->copy()->subDays(29)->toDateString();
        $baris = CatatanSalat::where('user_id', $uid)
            ->whereBetween('tanggal', [$dari, $hari->toDateString()])
            ->get(['tanggal', 'waktu'])
            ->groupBy(fn ($r) => substr((string) $r->tanggal, 0, 10))
            ->map(fn ($g) => $g->pluck('waktu')->unique()->values()->all());

        $minggu = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $hari->copy()->subDays($i)->toDateString();
            $minggu[] = ['tanggal' => $d, 'jumlah' => count($baris[$d] ?? [])];
        }

        // Rangkaian hari dengan 5 salat lengkap. Hari ini yang belum lengkap tidak memutus rangkaian.
        $rangkaian = 0;
        for ($i = 0; $i < 30; $i++) {
            $d = $hari->copy()->subDays($i)->toDateString();
            if (count($baris[$d] ?? []) >= 5) {
                $rangkaian++;
            } elseif ($i > 0) {
                break;
            }
        }

        return [
            'tanggal' => $tanggal,
            'hari' => $baris[$tanggal] ?? [],
            'minggu' => $minggu,
            'total7' => array_sum(array_column($minggu, 'jumlah')),
            'rangkaian' => $rangkaian,
        ];
    }
}
