@extends('layouts.halaman')
@section('judul', 'Saran pengguna')

@section('isi')
<div class="k">
    @include('admin._menu')

    <div class="baris">
        <h2 style="font-size:18px;font-weight:500">Saran pengguna</h2>
    </div>

    <div>
        <a class="ch {{ $status ? '' : 'a' }}" href="{{ route('admin.saran.index') }}" style="text-decoration:none">Semua ({{ $jumlah->sum() }})</a>
        @foreach (\App\Models\Saran::STATUS as $kode => $nama)
            <a class="ch {{ $status === $kode ? 'a' : '' }}" href="{{ route('admin.saran.index', ['status' => $kode]) }}" style="text-decoration:none">{{ $nama }} ({{ $jumlah[$kode] ?? 0 }})</a>
        @endforeach
    </div>

    @forelse ($saran as $s)
        <div class="item">
            <div>
                <b>{{ $s->user->name ?? 'Pengguna dihapus' }}</b>
                <span class="sub">· {{ $s->created_at->translatedFormat('j F Y H:i') }}</span>
                <span class="ch" style="cursor:default;margin-left:6px">{{ $s->kategori }}</span>
            </div>
            <p style="white-space:pre-wrap;word-break:break-word;margin:0">{{ $s->isi }}</p>

            <form class="form" method="post" action="{{ route('admin.saran.update', $s) }}">
                @csrf @method('PUT')
                <label for="st{{ $s->id }}" style="margin-top:4px">Status</label>
                <select id="st{{ $s->id }}" name="status">
                    @foreach (\App\Models\Saran::STATUS as $kode => $nama)
                        <option value="{{ $kode }}" @selected($s->status === $kode)>{{ $nama }}</option>
                    @endforeach
                </select>
                <label for="bl{{ $s->id }}">Balasan untuk pengguna (opsional)</label>
                <textarea id="bl{{ $s->id }}" name="balasan" rows="2" maxlength="1000">{{ $s->balasan }}</textarea>
                <div class="ab" style="margin-top:8px">
                    <button type="submit"><i class="ti ti-device-floppy" aria-hidden="true"></i>Simpan</button>
                </div>
            </form>
            <form method="post" action="{{ route('admin.saran.destroy', $s) }}" onsubmit="return confirm('Hapus saran ini?')">
                @csrf @method('DELETE')
                <div class="ab"><button type="submit" class="f"><i class="ti ti-trash" aria-hidden="true"></i>Hapus</button></div>
            </form>
        </div>
    @empty
        <div class="kosong">Belum ada saran.</div>
    @endforelse

    @if ($saran->hasPages())
        <div class="hal">
            @if ($saran->onFirstPage()) <span>‹ Sebelumnya</span> @else <a href="{{ $saran->previousPageUrl() }}">‹ Sebelumnya</a> @endif
            <span>{{ $saran->currentPage() }} / {{ $saran->lastPage() }}</span>
            @if ($saran->hasMorePages()) <a href="{{ $saran->nextPageUrl() }}">Berikutnya ›</a> @else <span>Berikutnya ›</span> @endif
        </div>
    @endif
</div>

<p style="margin-top:10px"><a class="tbl" href="{{ route('beranda') }}">← Kembali ke aplikasi</a></p>
@endsection
