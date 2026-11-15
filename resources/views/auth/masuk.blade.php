@extends('layouts.halaman')
@section('judul', 'Masuk')

@section('isi')
<form class="k form" method="post" action="{{ route('masuk.proses') }}">
    @csrf
    <h2>Masuk</h2>
    <p class="sub">Favorit doa, kota, dan catatan salat Anda ikut tersimpan di semua perangkat.</p>

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
    @error('email') <p class="salah">{{ $message }}</p> @enderror

    <label for="password">Kata sandi</label>
    <input id="password" type="password" name="password" autocomplete="current-password" required>
    @error('password') <p class="salah">{{ $message }}</p> @enderror

    <label class="cek"><input type="checkbox" name="ingat" value="1"> Ingat saya</label>

    <button class="bt" type="submit">Masuk</button>
    <p class="sub tengah">Belum punya akun? <a href="{{ route('daftar') }}">Daftar</a></p>
</form>
@endsection
