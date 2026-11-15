@extends('layouts.halaman')
@section('judul', 'Kelola Asmaul Husna')

@section('isi')
<div class="k">
    @include('admin._menu')

    <div class="baris">
        <h2>Asmaul Husna</h2>
        <a class="bt kecil" href="{{ route('admin.asma.create') }}"><i class="ti ti-plus" aria-hidden="true"></i>Tambah</a>
    </div>

    <form method="get" class="form" role="search">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nomor, nama, atau arti" aria-label="Cari Asmaul Husna">
    </form>

    @forelse ($asma as $a)
        <div class="item">
            <div>
                <span class="sub">{{ $a->nomor }}.</span>
                <b>{{ $a->latin }}</b>
                <span class="sub">· {{ $a->arti }}</span>
            </div>
            <div class="ab">
                <a class="tbl" href="{{ route('admin.asma.edit', $a) }}"><i class="ti ti-pencil" aria-hidden="true"></i>Ubah</a>
                <form method="post" action="{{ route('admin.asma.destroy', $a) }}" onsubmit="return confirm('Hapus nama ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="f"><i class="ti ti-trash" aria-hidden="true"></i>Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="kosong">Belum ada data.</div>
    @endforelse

    @if ($asma->hasPages())
        <div class="hal">
            @if ($asma->onFirstPage()) <span>‹ Sebelumnya</span> @else <a href="{{ $asma->previousPageUrl() }}">‹ Sebelumnya</a> @endif
            <span>{{ $asma->currentPage() }} / {{ $asma->lastPage() }}</span>
            @if ($asma->hasMorePages()) <a href="{{ $asma->nextPageUrl() }}">Berikutnya ›</a> @else <span>Berikutnya ›</span> @endif
        </div>
    @endif
</div>

<p style="margin-top:10px"><a class="tbl" href="{{ route('beranda') }}#asma">← Kembali ke aplikasi</a></p>
@endsection
