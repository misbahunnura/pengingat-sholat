<?php

namespace App\Http\Controllers;

use App\Models\Saran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaranController extends Controller
{
    public function index(Request $request): Response
    {
        $saran = Saran::where('user_id', $request->user()->id)->latest()->get();

        // Daftar dirender dulu (penanda "Baru" masih ada), setelah itu dianggap sudah dilihat.
        $html = view('saran.index', ['saran' => $saran])->render();

        Saran::where('user_id', $request->user()->id)->where('perlu_dilihat', true)->update(['perlu_dilihat' => false]);

        return response($html);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'kategori' => ['required', Rule::in(Saran::KATEGORI)],
            'isi' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        Saran::create($v + ['user_id' => $request->user()->id]);

        return redirect()->route('saran.index')->with('ok', 'Terima kasih, saranmu sudah terkirim.');
    }
}
