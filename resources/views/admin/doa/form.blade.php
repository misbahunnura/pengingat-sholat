@extends('layouts.halaman')
@section('judul', $doa->exists ? 'Ubah doa' : 'Tambah doa')

@section('isi')
<form class="k form" method="post"
      action="{{ $doa->exists ? route('admin.doa.update', $doa) : route('admin.doa.store') }}">
    @csrf
    @if ($doa->exists) @method('PUT') @endif
    <h2>{{ $doa->exists ? 'Ubah doa' : 'Tambah doa' }}</h2>

    <label for="judul">Judul</label>
    <input id="judul" name="judul" value="{{ old('judul', $doa->judul) }}" maxlength="150" required>
    @error('judul') <p class="salah">{{ $message }}</p> @enderror

    <label for="kategori">Kategori</label>
    <input id="kategori" name="kategori" value="{{ old('kategori', $doa->kategori) }}" maxlength="60" list="daftar-kategori" required>
    <datalist id="daftar-kategori">
        @foreach ($kategori as $k) <option value="{{ $k }}"> @endforeach
    </datalist>
    @error('kategori') <p class="salah">{{ $message }}</p> @enderror

    <label for="arab">Teks Arab</label>
    <textarea id="arab" name="arab" dir="rtl" lang="ar" rows="3" required>{{ old('arab', $doa->arab) }}</textarea>
    @error('arab') <p class="salah">{{ $message }}</p> @enderror

    <label for="latin">Latin</label>
    <textarea id="latin" name="latin" rows="3" required>{{ old('latin', $doa->latin) }}</textarea>
    @error('latin') <p class="salah">{{ $message }}</p> @enderror

    <label for="arti">Arti</label>
    <textarea id="arti" name="arti" rows="3" required>{{ old('arti', $doa->arti) }}</textarea>
    @error('arti') <p class="salah">{{ $message }}</p> @enderror

    <button class="bt" type="submit">Simpan</button>
    <p class="sub tengah"><a href="{{ route('admin.doa.index') }}">Batal</a></p>
</form>
@endsection
