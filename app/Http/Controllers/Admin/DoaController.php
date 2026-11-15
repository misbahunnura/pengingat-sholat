<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $doa = Doa::query()
            ->when($q !== '', fn ($w) => $w->where(function ($x) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
                $x->where('judul', 'like', $like)->orWhere('kategori', 'like', $like);
            }))
            ->orderBy('kategori')->orderBy('judul')
            ->paginate(15)->withQueryString();

        return view('admin.doa.index', ['doa' => $doa, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.doa.form', ['doa' => new Doa, 'kategori' => $this->kategori()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Doa::create($this->validasi($request));

        return redirect()->route('admin.doa.index')->with('ok', 'Doa ditambahkan.');
    }

    public function edit(Doa $doa): View
    {
        return view('admin.doa.form', ['doa' => $doa, 'kategori' => $this->kategori()]);
    }

    public function update(Request $request, Doa $doa): RedirectResponse
    {
        $doa->update($this->validasi($request));

        return redirect()->route('admin.doa.index')->with('ok', 'Doa diperbarui.');
    }

    public function destroy(Doa $doa): RedirectResponse
    {
        $doa->delete(); // favorit pengguna ikut terhapus (cascade)

        return redirect()->route('admin.doa.index')->with('ok', 'Doa dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:60'],
            'arab' => ['required', 'string', 'max:3000'],
            'latin' => ['required', 'string', 'max:3000'],
            'arti' => ['required', 'string', 'max:3000'],
        ]);
    }

    private function kategori(): array
    {
        return Doa::query()->distinct()->orderBy('kategori')->pluck('kategori')->all();
    }
}
