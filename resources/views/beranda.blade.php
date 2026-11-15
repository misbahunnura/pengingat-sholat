<!doctype html>
<html lang="id" data-t="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Salat">
    <title>{{ config('app.name') }} — Jadwal Salat, Al-Quran &amp; Doa Harian</title>
    <meta name="description" content="Jadwal salat, pengingat azan, Al-Quran, dzikir pagi petang, tasbih digital, arah kiblat, doa harian, dan pencatat ibadah.">
    <script>
        // Pasang tema sebelum halaman digambar agar tidak berkedip
        try {
            var t = JSON.parse(localStorage.getItem('ps:tema'));
            if (t === 'light' || t === 'dark') document.documentElement.dataset.t = t;
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.42.0/dist/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
</head>
<body>
<div class="w" id="w">
    <h1 class="sr-only">{{ config('app.name') }}</h1>

    <div class="top">
        <a class="lg" href="#beranda"><i class="ti ti-building-mosque" aria-hidden="true"></i>Pengingat Salat</a>
        <button class="ic dot" id="lonceng" aria-label="Status pengingat"><i class="ti ti-bell-off" id="ilon" style="font-size:17px" aria-hidden="true"></i><b></b></button>
        <button class="ic" id="tg" aria-label="Ganti tema"><i class="ti ti-sun" id="ik" style="font-size:17px" aria-hidden="true"></i></button>
        @auth
            @if (auth()->user()->is_admin)
                <a class="ic" href="{{ route('admin.saran.index') }}" aria-label="Saran pengguna{{ ($lencanaSaran ?? 0) > 0 ? ', '.$lencanaSaran.' baru' : '' }}" title="Saran pengguna"><i class="ti ti-message-2" style="font-size:17px" aria-hidden="true"></i>@if (($lencanaSaran ?? 0) > 0)<span class="lcn">{{ $lencanaSaran > 99 ? '99+' : $lencanaSaran }}</span>@endif</a>
                <a class="ic" href="{{ route('admin.doa.index') }}" aria-label="Kelola konten" title="Kelola konten"><i class="ti ti-settings" style="font-size:17px" aria-hidden="true"></i></a>
            @endif
            <form method="post" action="{{ route('keluar') }}" style="display:contents">
                @csrf
                <button class="ic" type="submit" aria-label="Keluar ({{ auth()->user()->name }})" title="Keluar ({{ auth()->user()->name }})"><i class="ti ti-logout" style="font-size:17px" aria-hidden="true"></i></button>
            </form>
        @else
            <a class="ic" href="{{ route('masuk') }}" aria-label="Masuk" title="Masuk"><i class="ti ti-user" style="font-size:17px" aria-hidden="true"></i></a>
        @endauth
    </div>

    {{-- ================= BERANDA ================= --}}
    <div class="pg" id="pg-beranda">
        <div class="k bn-kartu">
            <div class="baris" style="margin:0">
                <div>
                    <p class="kecil">SALAT BERIKUTNYA</p>
                    <p class="bn-nama" id="bn-nama">Memuat…</p>
                    <p class="sub" id="bn-sisa" style="margin:2px 0 0"></p>
                </div>
                <a class="ic" href="#jadwal" aria-label="Buka jadwal"><i class="ti ti-clock" style="font-size:17px" aria-hidden="true"></i></a>
            </div>
        </div>

        <div class="k">
            <div class="baris" style="margin:0">
                <div style="flex:1">
                    <p class="kecil">MISI HARI INI</p>
                    <p id="misi-teks" style="font-size:14px;font-weight:500;margin:4px 0 8px">Ayo lengkapi target ibadahmu!</p>
                    <div class="pbar"><div id="misi-bar" style="width:0%"></div></div>
                </div>
                <a class="ic" href="#tracker" aria-label="Buka tracker"><i class="ti ti-checklist" style="font-size:17px" aria-hidden="true"></i></a>
            </div>
        </div>

        @auth
            @if (($lencanaSaran ?? 0) > 0)
                <a class="k nt" href="{{ auth()->user()->is_admin ? route('admin.saran.index') : route('saran.index') }}">
                    <span class="nt-i"><i class="ti ti-message-2" aria-hidden="true"></i></span>
                    <div style="flex:1;min-width:0">
                        @if (auth()->user()->is_admin)
                            <b>{{ $lencanaSaran }} saran baru menunggu</b>
                            <span class="sub">Ketuk untuk membaca dan membalas.</span>
                        @else
                            <b>{{ $lencanaSaran }} saranmu mendapat tanggapan</b>
                            <span class="sub">Admin membalas atau memperbarui statusnya.</span>
                        @endif
                    </div>
                    <i class="ti ti-chevron-right" aria-hidden="true"></i>
                </a>
            @endif
        @endauth

        <div class="k nt" id="pasang" hidden>
            <span class="nt-i"><i class="ti ti-download" aria-hidden="true"></i></span>
            <div style="flex:1;min-width:0">
                <b>Pasang di layar utama</b>
                <span class="sub" id="pasang-teks">Buka seperti aplikasi dan tetap bisa dipakai walau sinyal lemah.</span>
            </div>
            <button type="button" class="bt kecil" id="pasang-btn" hidden>Pasang</button>
            <button type="button" class="ic" id="pasang-x" aria-label="Tutup"><i class="ti ti-x" style="font-size:15px" aria-hidden="true"></i></button>
        </div>

        <p class="jdl" style="margin-top:16px">Akses Cepat</p>
        <div class="aks" id="aks"></div>
    </div>

    {{-- ================= AL-QURAN ================= --}}
    <div class="pg" id="pg-quran" hidden>
        <div class="sh"><i class="ti ti-book-2" aria-hidden="true"></i><div><b>Al-Quran</b><span>Baca dan dengarkan lantunan suci.</span></div></div>
        <div id="qr-daftar">
            <div id="qr-lanjut"></div>
            <div class="cari">
                <i class="ti ti-search" aria-hidden="true"></i>
                <input id="qr-cari" type="search" placeholder="Cari surah…" autocomplete="off" aria-label="Cari surah">
            </div>
            <div id="qr-list"><div class="kosong">Memuat daftar surah…</div></div>
        </div>
        <div id="qr-detail" hidden></div>
    </div>

    {{-- ================= JADWAL ================= --}}
    <div class="pg" id="p1" hidden>
        <div class="hero">
            <svg aria-hidden="true"><defs><pattern id="pt" width="48" height="48" patternUnits="userSpaceOnUse"><rect x="12" y="12" width="24" height="24" fill="none" stroke="#fff" stroke-width="1.5"></rect><rect x="12" y="12" width="24" height="24" fill="none" stroke="#fff" stroke-width="1.5" transform="rotate(45 24 24)"></rect></pattern></defs><rect width="100%" height="100%" fill="url(#pt)"></rect></svg>
            <p style="font-family:'Amiri',serif;font-size:19px;color:var(--emas);margin:0">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
            <p id="info" style="font-size:12px;opacity:.75;margin:2px 0 12px">Memuat jadwal…</p>
            <p id="nama" style="font-size:24px;font-weight:500;margin:0">&nbsp;</p>
            <p id="hitung" style="font-size:48px;font-weight:500;margin:0;letter-spacing:1px;line-height:1.2">00:00:00</p>
            <div class="bar"><div id="pb" style="width:0%"></div></div>
            <p id="plabel" style="font-size:12px;opacity:.75;margin:0"></p>
            <button class="bt" id="btn"><i class="ti ti-bell" id="ibtn" aria-hidden="true"></i><span id="tbtn">Aktifkan pengingat + azan</span></button>
            <p id="st" style="font-size:12px;opacity:.85;margin:8px 0 0">Belum aktif.</p>
        </div>

        <div class="err" id="err" hidden>
            <span id="errt"></span>
            <button id="ulang" type="button">Coba lagi</button>
        </div>

        <div class="cari">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input id="kota" type="search" placeholder="Cari kota, misalnya Medan" autocomplete="off" aria-label="Cari kota">
            <div class="dd" id="dd" hidden></div>
        </div>

        <div class="g" id="grid"></div>
        <div class="cat"><span id="imsak"></span><span>·</span><span>Hari Jumat: Zuhur diganti salat Jumat</span></div>

        <button id="gps" type="button" style="margin-top:10px;background:var(--chip);color:var(--chipt);border:0;border-radius:20px;padding:8px 14px;font-size:13px"><i class="ti ti-current-location" style="vertical-align:-2px;margin-right:6px" aria-hidden="true"></i>Pakai lokasi saya</button>
        <span id="gpst" style="font-size:12px;color:var(--sub);margin-left:8px"></span>
    </div>

    {{-- ================= DOA / FAVORIT ================= --}}
    <div class="pg" id="p2" hidden>
        <div class="k">
            <p id="jd" style="font-size:18px;font-weight:500;margin:0 0 8px">Kumpulan doa harian</p>
            <input id="cd" type="search" placeholder="Cari doa..." aria-label="Cari doa" style="background:var(--kartu);color:var(--teks);border:1px solid var(--garis);border-radius:10px">
            <div style="margin-top:10px" id="chips"></div>
        </div>
        <div id="lst"></div>
    </div>

    {{-- ================= TRACKER ================= --}}
    <div class="pg" id="pg-tracker" hidden>
        <div class="sh"><i class="ti ti-checklist" aria-hidden="true"></i><div><b>Tracker Ibadah</b><span>Catat ibadah harianmu.</span></div></div>

        @auth
            <section class="k catatan" aria-labelledby="cat-judul" style="margin-top:0">
                <p id="cat-judul" class="jdl">Catatan salat hari ini</p>
                <div class="cg" id="catgrid" role="group" aria-label="Tandai salat yang sudah dikerjakan"></div>
                <div class="mg" id="catmgg" aria-hidden="true"></div>
                <p class="sub" id="catinfo" style="margin:8px 0 0">Memuat…</p>
            </section>
        @else
            <section class="k catatan" style="margin-top:0">
                <p class="jdl">Catatan salat</p>
                <p class="sub" style="margin:4px 0 0"><a href="{{ route('masuk') }}">Masuk</a> atau <a href="{{ route('daftar') }}">daftar</a> untuk mencatat salat harian dan melihat rangkaian hari lengkap.</p>
            </section>
        @endauth

        <section class="k">
            <p class="jdl">Ibadah sunnah hari ini</p>
            <div id="tr-misi" class="cg2"></div>
            <div class="mg" id="tr-mgg" aria-hidden="true" style="margin-top:14px"></div>
            <p class="sub" id="tr-info" style="margin:8px 0 0"></p>
        </section>
    </div>

    {{-- ================= DZIKIR PAGI & PETANG ================= --}}
    <div class="pg" id="pg-dzikir" hidden>
        <div class="sh"><i class="ti ti-sunset-2" aria-hidden="true"></i><div><b>Dzikir Pagi &amp; Petang</b><span>Ketuk lingkaran untuk menghitung bacaan.</span></div></div>
        <div class="dz-tab" role="tablist" aria-label="Waktu dzikir">
            <button type="button" role="tab" data-w="pagi"><i class="ti ti-sun" aria-hidden="true"></i>Pagi</button>
            <button type="button" role="tab" data-w="petang"><i class="ti ti-moon" aria-hidden="true"></i>Petang</button>
        </div>
        <div class="k dz-ring">
            <div class="baris" style="margin:0 0 8px">
                <p class="sub" id="dz-info" style="margin:0">Memuat…</p>
                <button type="button" class="tbl" id="dz-reset"><i class="ti ti-refresh" aria-hidden="true"></i>Ulangi dari awal</button>
            </div>
            <div class="pbar"><div id="dz-bar" style="width:0%"></div></div>
        </div>
        <div id="dz-list"></div>
    </div>

    {{-- ================= TASBIH ================= --}}
    <div class="pg" id="pg-tasbih" hidden>
        <div class="sh"><i class="ti ti-fingerprint" aria-hidden="true"></i><div><b>Tasbih Digital</b><span>Perbanyak mengingat Allah.</span></div></div>
        <div class="chs" id="tb-pilih" role="tablist" aria-label="Pilih dzikir"></div>
        <div class="tb-info">
            <div style="flex:1;min-width:0">
                <p class="tb-arab" id="tb-arab"></p>
                <p class="tb-latin" id="tb-latin"></p>
            </div>
            <div class="tb-target"><span>TARGET</span><b id="tb-target">33</b></div>
        </div>
        <div class="k tb-arti"><p class="kecil" style="margin:0 0 2px">ARTI</p><p id="tb-arti" class="sub" style="margin:0"></p></div>
        <div class="tb-wrap">
            <button type="button" class="tb-btn" id="tb-tap" aria-label="Tambah hitungan">
                <span class="tb-ring" id="tb-ring"></span>
                <b id="tb-n">0</b>
                <small><i class="ti ti-hand-finger" aria-hidden="true"></i> TAP</small>
            </button>
        </div>
        <div class="tengah"><button type="button" class="tbl" id="tb-reset"><i class="ti ti-refresh" aria-hidden="true"></i>Reset</button></div>
    </div>

    {{-- ================= ASMAUL HUSNA ================= --}}
    <div class="pg" id="pg-asma" hidden>
        <div class="sh"><i class="ti ti-sparkles" aria-hidden="true"></i><div><b>Asmaul Husna</b><span>99 nama Allah yang indah.</span></div></div>
        <div class="cari">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input id="as-cari" type="search" placeholder="Cari nama atau arti…" autocomplete="off" aria-label="Cari Asmaul Husna">
        </div>
        <div id="as-list"></div>
    </div>

    {{-- ================= KIBLAT ================= --}}
    <div class="pg" id="pg-kiblat" hidden>
        <div class="sh"><i class="ti ti-compass" aria-hidden="true"></i><div><b>Arah Kiblat</b><span id="kb-lok">Dari lokasimu ke Ka'bah.</span></div></div>
        <div class="kb-wrap">
            <div class="kb-dial" id="kb-dial">
                <span class="kb-n">U</span><span class="kb-e">T</span><span class="kb-s">S</span><span class="kb-b">B</span>
                <div class="kb-mark" id="kb-mark"><i class="ti ti-building-mosque" aria-hidden="true"></i></div>
            </div>
            <div class="kb-ptr" aria-hidden="true"></div>
        </div>
        <p class="kb-der" id="kb-der">—</p>
        <p class="sub tengah" id="kb-ket">Arah kiblat dihitung dari kota yang kamu pilih di menu Jadwal.</p>
        <div class="tengah"><button type="button" class="bt kecil" id="kb-aktif"><i class="ti ti-compass" aria-hidden="true"></i>Aktifkan kompas</button></div>
    </div>

    {{-- ================= MASJID ================= --}}
    <div class="pg" id="pg-masjid" hidden>
        <div class="sh"><i class="ti ti-map-pin" aria-hidden="true"></i><div><b>Masjid Terdekat</b><span>Cari masjid di sekitarmu.</span></div></div>
        <div class="k">
            <p class="sub" id="ms-lok" style="margin:0 0 10px"></p>
            <div class="ab" style="flex-wrap:wrap">
                <button type="button" id="ms-gps"><i class="ti ti-current-location" aria-hidden="true"></i>Pakai lokasi saya</button>
                <a class="tbl" id="ms-peta" target="_blank" rel="noopener"><i class="ti ti-map-2" aria-hidden="true"></i>Buka di Google Maps</a>
            </div>
        </div>
        <div id="ms-list"><div class="kosong">Memuat…</div></div>
    </div>

    <nav class="tabs" aria-label="Menu utama">
        <a href="#beranda" data-n="beranda"><i class="ti ti-home" aria-hidden="true"></i>Beranda</a>
        <a href="#quran" data-n="quran"><i class="ti ti-book-2" aria-hidden="true"></i>Al-Quran</a>
        <a href="#jadwal" data-n="jadwal"><i class="ti ti-clock" aria-hidden="true"></i>Jadwal</a>
        <a href="#tracker" data-n="tracker"><i class="ti ti-checklist" aria-hidden="true"></i>Tracker</a>
    </nav>

    <canvas id="cf" aria-hidden="true"></canvas>
    <audio id="azan" preload="none" src="{{ asset('audio/azan.mp3') }}"></audio>
</div>

<script>
    window.PS = @json($konfig);
</script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/fitur.js') }}"></script>
<script src="{{ asset('js/pwa.js') }}"></script>
</body>
</html>
