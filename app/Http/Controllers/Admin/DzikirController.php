<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dzikir;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DzikirController extends Controller
{
    public function index(Request $request): View
    {
        $waktu = $request->query('waktu');
        if (! array_key_exists((string) $waktu, Dzikir::WAKTU)) {
            $waktu = null;
        }

        $dzikir = Dzikir::query()
            ->when($waktu, fn ($q) => $q->where('waktu', $waktu))
            ->orderBy('urutan')->orderBy('id')
            ->paginate(15)->withQueryString();

        return view('admin.dzikir.index', ['dzikir' => $dzikir, 'waktu' => $waktu]);
    }

    public function create(): View
    {
        $dzikir = new Dzikir(['waktu' => 'keduanya', 'jumlah' => 1, 'urutan' => ((int) Dzikir::max('urutan')) + 1]);

        return view('admin.dzikir.form', ['dzikir' => $dzikir]);
    }

    public function store(Request $request): RedirectResponse
    {
        Dzikir::create($this->validasi($request));

        return redirect()->route('admin.dzikir.index')->with('ok', 'Dzikir ditambahkan.');
    }

    public function edit(Dzikir $dzikir): View
    {
        return view('admin.dzikir.form', ['dzikir' => $dzikir]);
    }

    public function update(Request $request, Dzikir $dzikir): RedirectResponse
    {
        $dzikir->update($this->validasi($request));

        return redirect()->route('admin.dzikir.index')->with('ok', 'Dzikir diperbarui.');
    }

    public function destroy(Dzikir $dzikir): RedirectResponse
    {
        $dzikir->delete();

        return redirect()->route('admin.dzikir.index')->with('ok', 'Dzikir dihapus.');
    }

    private function validasi(Request $request): array
    {
        $v = $request->validate([
            'waktu' => ['required', Rule::in(array_keys(Dzikir::WAKTU))],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'judul' => ['required', 'string', 'max:150'],
            'arab' => ['required', 'string', 'max:5000'],
            'latin' => ['required', 'string', 'max:5000'],
            'arti' => ['required', 'string', 'max:5000'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000'],
            'sumber' => ['nullable', 'string', 'max:150'],
        ]);

        $v['urutan'] = $v['urutan'] ?? 0;
        $v['sumber'] = ($v['sumber'] ?? '') !== '' ? $v['sumber'] : null;

        return $v;
    }
}
