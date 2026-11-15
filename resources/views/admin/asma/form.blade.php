@extends('layouts.halaman')
@section('judul', $asma->exists ? 'Ubah nama' : 'Tambah nama')

@section('isi')
<form class="k form" method="post"
      action="{{ $asma->exists ? route('admin.asma.update', $asma) : route('admin.asma.store') }}">
    @csrf
    @if ($asma->exists) @method('PUT') @endif
    <h2>{{ $asma->exists ? 'Ubah nama' : 'Tambah nama' }}</h2>

    <label for="nomor">Nomor urut</label>
    <input id="nomor" name="nomor" type="number" min="1" max="999" value="{{ old('nomor', $asma->nomor) }}" required>
    @error('nomor') <p class="salah">{{ $message }}</p> @enderror

    <label for="arab">Teks Arab</label>
    <input id="arab" name="arab" dir="rtl" lang="ar" value="{{ old('arab', $asma->arab) }}" maxlength="100" required>
    @error('arab') <p class="salah">{{ $message }}</p> @enderror

    <label for="latin">Latin</label>
    <input id="latin" name="latin" value="{{ old('latin', $asma->latin) }}" maxlength="100" required>
    @error('latin') <p class="salah">{{ $message }}</p> @enderror

    <label for="arti">Arti</label>
    <input id="arti" name="arti" value="{{ old('arti', $asma->arti) }}" maxlength="200" required>
    @error('arti') <p class="salah">{{ $message }}</p> @enderror

    <button class="bt" type="submit">Simpan</button>
    <p class="sub tengah"><a href="{{ route('admin.asma.index') }}">Batal</a></p>
</form>
@endsection
