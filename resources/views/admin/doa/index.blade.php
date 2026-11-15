@extends('layouts.halaman')
@section('judul', 'Kelola doa')

@section('isi')
<div class="k">
    @include('admin._menu')

    <div class="baris">
        <h2>Kelola doa</h2>
        <a class="bt kecil" href="{{ route('admin.doa.create') }}"><i class="ti ti-plus" aria-hidden="true"></i>Tambah</a>
    </div>

    <form method="get" class="form" role="search">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari judul atau kategori" aria-label="Cari doa">
    </form>

    @forelse ($doa as $d)
        <div class="item">
            <div>
                <b>{{ $d->judul }}</b>
                <span class="ch" style="cursor:default">{{ $d->kategori }}</span>
            </div>
            <div class="ab">
                <a class="tbl" href="{{ route('admin.doa.edit', $d) }}"><i class="ti ti-pencil" aria-hidden="true"></i>Ubah</a>
                <form method="post" action="{{ route('admin.doa.destroy', $d) }}" onsubmit="return confirm('Hapus doa ini? Favorit pengguna untuk doa ini ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="f"><i class="ti ti-trash" aria-hidden="true"></i>Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="kosong">Belum ada doa.</div>
    @endforelse

    @if ($doa->hasPages())
        <div class="hal">
            @if ($doa->onFirstPage()) <span>‹ Sebelumnya</span> @else <a href="{{ $doa->previousPageUrl() }}">‹ Sebelumnya</a> @endif
            <span>{{ $doa->currentPage() }} / {{ $doa->lastPage() }}</span>
            @if ($doa->hasMorePages()) <a href="{{ $doa->nextPageUrl() }}">Berikutnya ›</a> @else <span>Berikutnya ›</span> @endif
        </div>
    @endif
</div>

<p style="margin-top:10px"><a class="tbl" href="{{ route('beranda') }}">← Kembali ke aplikasi</a></p>
@endsection
