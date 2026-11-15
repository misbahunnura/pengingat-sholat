<!doctype html>
<html lang="id" data-t="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <title>@yield('judul') — {{ config('app.name') }}</title>
    <script>
        try {
            var t = JSON.parse(localStorage.getItem('ps:tema'));
            if (t === 'light' || t === 'dark') document.documentElement.dataset.t = t;
        } catch (e) {}
    </script>
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.42.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="w">
    <div class="top">
        <a class="lg" href="{{ route('beranda') }}"><i class="ti ti-building-mosque" aria-hidden="true"></i>Pengingat Salat</a>
        @auth
            @if (auth()->user()->is_admin)
                <a class="ic" href="{{ route('admin.saran.index') }}" aria-label="Saran pengguna{{ ($lencanaSaran ?? 0) > 0 ? ', '.$lencanaSaran.' baru' : '' }}"><i class="ti ti-message-2" aria-hidden="true"></i>@if (($lencanaSaran ?? 0) > 0)<span class="lcn">{{ $lencanaSaran > 99 ? '99+' : $lencanaSaran }}</span>@endif</a>
                <a class="ic" href="{{ route('admin.doa.index') }}" aria-label="Kelola konten"><i class="ti ti-settings" aria-hidden="true"></i></a>
            @endif
        @endauth
    </div>

    @if (session('ok'))
        <div class="info-ok" role="status">{{ session('ok') }}</div>
    @endif

    @yield('isi')
</div>
<script src="{{ asset('js/pwa.js') }}"></script>
</body>
</html>
