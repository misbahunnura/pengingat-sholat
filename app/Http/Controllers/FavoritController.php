<?php

namespace App\Http\Controllers;

use App\Models\Doa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoritController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['ids' => $this->ids($request)]);
    }

    /** Gabungkan favorit yang tersimpan di peramban ke akun (dipanggil sekali setelah login). */
    public function sinkron(Request $request): JsonResponse
    {
        $v = $request->validate([
            'ids' => ['present', 'array', 'max:500'],
            'ids.*' => ['integer'],
        ]);

        $valid = Doa::whereIn('id', $v['ids'])->pluck('id')->all();
        $request->user()->favorit()->syncWithoutDetaching($valid);

        return response()->json(['ids' => $this->ids($request)]);
    }

    public function simpan(Request $request, Doa $doa): JsonResponse
    {
        $request->user()->favorit()->syncWithoutDetaching([$doa->id]);

        return response()->json(['ids' => $this->ids($request)]);
    }

    public function hapus(Request $request, Doa $doa): JsonResponse
    {
        $request->user()->favorit()->detach($doa->id);

        return response()->json(['ids' => $this->ids($request)]);
    }

    private function ids(Request $request): array
    {
        return $request->user()->favorit()->pluck('doa.id')->map(fn ($i) => (int) $i)->values()->all();
    }
}
