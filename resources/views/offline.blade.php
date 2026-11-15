<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <title>Sedang offline — {{ config('app.name') }}</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#000000;color:#e8ecf5;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;text-align:center;padding:24px}
        main{max-width:340px}
        .i{font-size:44px;margin-bottom:8px}
        h1{font-size:20px;font-weight:500;margin:0 0 8px}
        p{font-size:14px;line-height:1.6;color:#9aa5bd;margin:0 0 18px}
        button{background:#e0b64a;color:#000000;border:0;border-radius:22px;padding:10px 22px;font-size:14px;font-weight:600;cursor:pointer}
    </style>
</head>
<body>
<main>
    <div class="i" aria-hidden="true">🌙</div>
    <h1>Sedang offline</h1>
    <p>Koneksi internet belum tersedia dan halaman ini belum tersimpan di perangkatmu. Buka aplikasi sekali saat online, setelah itu jadwal terakhir tetap bisa dilihat walau sinyal lemah.</p>
    <button type="button" onclick="location.reload()">Coba lagi</button>
</main>
</body>
</html>
