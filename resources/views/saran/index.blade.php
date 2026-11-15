@extends('layouts.halaman')
@section('judul', 'Saran')

@section('isi')
<form class="k form" method="post" action="{{ route('saran.store') }}">
    @csrf
    <h2>Beri saran</h2>
    <p class="sub">Ada fitur yang kurang atau yang ingin diperbaiki? Tulis di sini, admin akan membacanya.</p>

    <label for="kategori">Jenis saran</label>
    <select id="kategori" name="kategori" required>
        @foreach (\App\Models\Saran::KATEGORI as $k)
            <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
        @endforeach
    </select>
    @error('kategori') <p class="salah">{{ $message }}</p> @enderror

    <label for="isi">Saranmu</label>
    <textarea id="isi" name="isi" rows="5" maxlength="1000" required placeholder="Misalnya: tolong tambah fitur …">{{ old('isi') }}</textarea>
    @error('isi') <p class="salah">{{ $message }}</p> @enderror

    <button class="bt" type="submit"><i class="ti ti-send" aria-hidden="true"></i>Kirim saran</button>
</form>

<div class="k">
    <p class="jdl">Saran yang pernah kamu kirim</p>
    @forelse ($saran as $s)
        <div class="item">
            <div>
                @if ($s->perlu_dilihat)
                    <span class="ch a" style="cursor:default;margin:0 6px 0 0"><i class="ti ti-bell-ringing" aria-hidden="true"></i> Ada tanggapan</span>
                @endif
                <span class="ch" style="cursor:default;margin:0 6px 0 0">{{ $s->kategori }}</span>
                <span class="ch {{ $s->status === 'selesai' ? 'a' : '' }}" style="cursor:default;margin:0">{{ \App\Models\Saran::STATUS[$s->status] ?? $s->status }}</span>
            </div>
            <p style="white-space:pre-wrap;word-break:break-word;margin:0">{{ $s->isi }}</p>
            @if ($s->balasan)
                <p class="sub" style="margin:0;white-space:pre-wrap"><b>Balasan admin:</b> {{ $s->balasan }}</p>
            @endif
            <p class="sub" style="margin:0;font-size:12px">{{ $s->created_at->translatedFormat('j F Y') }}</p>
        </div>
    @empty
        <div class="kosong">Belum ada saran.</div>
    @endforelse
</div>

<p style="margin-top:10px"><a class="tbl" href="{{ route('beranda') }}">← Kembali ke aplikasi</a></p>
@endsection
