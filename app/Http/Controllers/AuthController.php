<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function formMasuk(): View
    {
        return view('auth.masuk');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($v, $request->boolean('ingat'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function formDaftar(): View
    {
        return view('auth.daftar');
    }

    public function daftar(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // is_admin tidak bisa diisi dari formulir (tidak ada di $fillable)
        $user = User::create($v);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }
}
