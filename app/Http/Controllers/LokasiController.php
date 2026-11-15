<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LokasiController extends Controller
{
    /** Simpan kota/lokasi pilihan agar ikut berpindah antar perangkat. */
    public function simpan(Request $request): JsonResponse
    {
        $v = $request->validate([
            'nama' => ['required', 'string', 'max:80'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
            'tz' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
        ]);

        $request->user()->update(['lokasi' => [
            'nama' => $v['nama'],
            'lat' => round((float) $v['lat'], 4),
            'lon' => round((float) $v['lon'], 4),
            'tz' => $v['tz'],
        ]]);

        return response()->json(['ok' => true]);
    }
}
