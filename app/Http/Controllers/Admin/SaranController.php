<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Saran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaranController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        if (! array_key_exists((string) $status, Saran::STATUS)) {
            $status = null;
        }

        $saran = Saran::with('user:id,name,email')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)->withQueryString();

        return view('admin.saran.index', [
            'saran' => $saran,
            'status' => $status,
            'jumlah' => Saran::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function update(Request $request, Saran $saran): RedirectResponse
    {
        $v = $request->validate([
            'status' => ['required', Rule::in(array_keys(Saran::STATUS))],
            'balasan' => ['nullable', 'string', 'max:1000'],
        ]);

        $saran->status = $v['status'];
        $saran->balasan = $v['balasan'] ?: null;

        // Ada perubahan status/balasan: beri tahu pengguna lewat lencana di aplikasinya
        if ($saran->isDirty(['status', 'balasan'])) {
            $saran->perlu_dilihat = true;
        }

        $saran->save();

        return back()->with('ok', 'Saran diperbarui.');
    }

    public function destroy(Saran $saran): RedirectResponse
    {
        $saran->delete();

        return back()->with('ok', 'Saran dihapus.');
    }
}
