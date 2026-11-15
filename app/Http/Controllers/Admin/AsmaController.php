<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsmaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $asma = Asma::query()
            ->when($q !== '', function ($w) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
                $w->where(function ($x) use ($like, $q) {
                    $x->where('latin', 'like', $like)->orWhere('arti', 'like', $like);
                    if (ctype_digit($q)) {
                        $x->orWhere('nomor', (int) $q);
                    }
                });
            })
            ->orderBy('nomor')
            ->paginate(20)->withQueryString();

        return view('admin.asma.index', ['asma' => $asma, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.asma.form', ['asma' => new Asma(['nomor' => ((int) Asma::max('nomor')) + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Asma::create($this->validasi($request));

        return redirect()->route('admin.asma.index')->with('ok', 'Nama ditambahkan.');
    }

    public function edit(Asma $asma): View
    {
        return view('admin.asma.form', ['asma' => $asma]);
    }

    public function update(Request $request, Asma $asma): RedirectResponse
    {
        $asma->update($this->validasi($request, $asma));

        return redirect()->route('admin.asma.index')->with('ok', 'Nama diperbarui.');
    }

    public function destroy(Asma $asma): RedirectResponse
    {
        $asma->delete();

        return redirect()->route('admin.asma.index')->with('ok', 'Nama dihapus.');
    }

    private function validasi(Request $request, ?Asma $asma = null): array
    {
        return $request->validate([
            'nomor' => ['required', 'integer', 'min:1', 'max:999', Rule::unique('asma', 'nomor')->ignore($asma?->id)],
            'arab' => ['required', 'string', 'max:100'],
            'latin' => ['required', 'string', 'max:100'],
            'arti' => ['required', 'string', 'max:200'],
        ]);
    }
}
