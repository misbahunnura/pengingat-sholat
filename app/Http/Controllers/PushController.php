<?php

namespace App\Http\Controllers;

use App\Models\LanggananPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PushController extends Controller
{
    public function langgan(Request $request): JsonResponse
    {
        $v = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:1000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
            'tz' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
            'nama' => ['nullable', 'string', 'max:80'],
        ]);

        LanggananPush::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $v['endpoint'])],
            [
                'user_id' => $request->user()?->id,
                'endpoint' => $v['endpoint'],
                'p256dh' => $v['keys']['p256dh'],
                'auth' => $v['keys']['auth'],
                'lat' => round((float) $v['lat'], 4),
                'lon' => round((float) $v['lon'], 4),
                'tz' => $v['tz'],
                'nama' => $v['nama'] ?? null,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function berhenti(Request $request): JsonResponse
    {
        $v = $request->validate(['endpoint' => ['required', 'string', 'max:1000']]);

        LanggananPush::where('endpoint_hash', hash('sha256', $v['endpoint']))->delete();

        return response()->json(['ok' => true]);
    }
}
