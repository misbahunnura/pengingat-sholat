@extends('layouts.halaman')
@section('judul', 'Daftar')

@section('isi')
<form class="k form" method="post" action="{{ route('daftar.proses') }}">
    @csrf
    <h2>Buat akun</h2>
    <p class="sub">Gratis. Dipakai untuk menyinkronkan favorit, kota, dan catatan salat.</p>

    <label for="name">Nama</label>
    <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="80" required autofocus>
    @error('name') <p class="salah">{{ $message }}</p> @enderror

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
    @error('email') <p class="salah">{{ $message }}</p> @enderror

    <label for="password">Kata sandi (minimal 8 karakter)</label>
    <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
    @error('password') <p class="salah">{{ $message }}</p> @enderror

    <label for="password_confirmation">Ulangi kata sandi</label>
    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>

    <button class="bt" type="submit">Daftar</button>
    <p class="sub tengah">Sudah punya akun? <a href="{{ route('masuk') }}">Masuk</a></p>
</form>
@endsection
