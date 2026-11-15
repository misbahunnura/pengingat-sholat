@extends('layouts.halaman')
@section('judul', $dzikir->exists ? 'Ubah dzikir' : 'Tambah dzikir')

@section('isi')
<form class="k form" method="post"
      action="{{ $dzikir->exists ? route('admin.dzikir.update', $dzikir) : route('admin.dzikir.store') }}">
    @csrf
    @if ($dzikir->exists) @method('PUT') @endif
    <h2>{{ $dzikir->exists ? 'Ubah dzikir' : 'Tambah dzikir' }}</h2>

    <label for="judul">Judul</label>
    <input id="judul" name="judul" value="{{ old('judul', $dzikir->judul) }}" maxlength="150" required>
    @error('judul') <p class="salah">{{ $message }}</p> @enderror

    <label for="waktu">Dibaca pada</label>
    <select id="waktu" name="waktu" required>
        @foreach (\App\Models\Dzikir::WAKTU as $kode => $nama)
            <option value="{{ $kode }}" @selected(old('waktu', $dzikir->waktu) === $kode)>{{ $nama }}</option>
        @endforeach
    </select>
    @error('waktu') <p class="salah">{{ $message }}</p> @enderror

    <label for="jumlah">Jumlah bacaan</label>
    <input id="jumlah" name="jumlah" type="number" min="1" max="1000" value="{{ old('jumlah', $dzikir->jumlah) }}" required>
    @error('jumlah') <p class="salah">{{ $message }}</p> @enderror

    <label for="urutan">Urutan tampil (angka kecil tampil lebih dulu)</label>
    <input id="urutan" name="urutan" type="number" min="0" max="9999" value="{{ old('urutan', $dzikir->urutan) }}">
    @error('urutan') <p class="salah">{{ $message }}</p> @enderror

    <label for="arab">Teks Arab</label>
    <textarea id="arab" name="arab" dir="rtl" lang="ar" rows="4" required>{{ old('arab', $dzikir->arab) }}</textarea>
    @error('arab') <p class="salah">{{ $message }}</p> @enderror

    <label for="latin">Latin</label>
    <textarea id="latin" name="latin" rows="3" required>{{ old('latin', $dzikir->latin) }}</textarea>
    @error('latin') <p class="salah">{{ $message }}</p> @enderror

    <label for="arti">Arti</label>
    <textarea id="arti" name="arti" rows="3" required>{{ old('arti', $dzikir->arti) }}</textarea>
    @error('arti') <p class="salah">{{ $message }}</p> @enderror

    <label for="sumber">Sumber (opsional)</label>
    <input id="sumber" name="sumber" value="{{ old('sumber', $dzikir->sumber) }}" maxlength="150" placeholder="Misalnya: HR. Al-Bukhari">
    @error('sumber') <p class="salah">{{ $message }}</p> @enderror

    <button class="bt" type="submit">Simpan</button>
    <p class="sub tengah"><a href="{{ route('admin.dzikir.index') }}">Batal</a></p>
</form>
@endsection
