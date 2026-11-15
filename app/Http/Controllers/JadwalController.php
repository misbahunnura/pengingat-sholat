<?php

namespace App\Http\Controllers;

use App\Services\JadwalSalat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalController extends Controller
{
    public function show(Request $request, JadwalSalat $layanan): JsonResponse
    {
        $v = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
            'tz' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
        ]);

        $data = $layanan->ambil((float) $v['lat'], (float) $v['lon'], $v['tz']);

        if (! $data) {
            return response()->json(['pesan' => 'Jadwal salat belum bisa dimuat. Coba lagi sebentar.'], 502);
        }

        return response()->json($data);
    }
}
