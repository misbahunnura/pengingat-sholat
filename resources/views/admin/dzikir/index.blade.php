@extends('layouts.halaman')
@section('judul', 'Kelola dzikir')

@section('isi')
<div class="k">
    @include('admin._menu')

    <div class="baris">
        <h2>Dzikir pagi &amp; petang</h2>
        <a class="bt kecil" href="{{ route('admin.dzikir.create') }}"><i class="ti ti-plus" aria-hidden="true"></i>Tambah</a>
    </div>

    <div>
        <a class="ch {{ $waktu ? '' : 'a' }}" href="{{ route('admin.dzikir.index') }}" style="text-decoration:none">Semua</a>
        @foreach (\App\Models\Dzikir::WAKTU as $kode => $nama)
            <a class="ch {{ $waktu === $kode ? 'a' : '' }}" href="{{ route('admin.dzikir.index', ['waktu' => $kode]) }}" style="text-decoration:none">{{ $nama }}</a>
        @endforeach
    </div>

    @forelse ($dzikir as $d)
        <div class="item">
            <div>
                <span class="sub">#{{ $d->urutan }}</span>
                <b>{{ $d->judul }}</b>
                <span class="ch" style="cursor:default">{{ \App\Models\Dzikir::WAKTU[$d->waktu] ?? $d->waktu }}</span>
                <span class="ch" style="cursor:default">{{ $d->jumlah }}×</span>
            </div>
            <div class="ab">
                <a class="tbl" href="{{ route('admin.dzikir.edit', $d) }}"><i class="ti ti-pencil" aria-hidden="true"></i>Ubah</a>
                <form method="post" action="{{ route('admin.dzikir.destroy', $d) }}" onsubmit="return confirm('Hapus dzikir ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="f"><i class="ti ti-trash" aria-hidden="true"></i>Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="kosong">Belum ada dzikir.</div>
    @endforelse

    @if ($dzikir->hasPages())
        <div class="hal">
            @if ($dzikir->onFirstPage()) <span>‹ Sebelumnya</span> @else <a href="{{ $dzikir->previousPageUrl() }}">‹ Sebelumnya</a> @endif
            <span>{{ $dzikir->currentPage() }} / {{ $dzikir->lastPage() }}</span>
            @if ($dzikir->hasMorePages()) <a href="{{ $dzikir->nextPageUrl() }}">Berikutnya ›</a> @else <span>Berikutnya ›</span> @endif
        </div>
    @endif
</div>

<p style="margin-top:10px"><a class="tbl" href="{{ route('beranda') }}#dzikir">← Kembali ke aplikasi</a></p>
@endsection
