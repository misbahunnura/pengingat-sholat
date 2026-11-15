/* Service worker Pengingat Salat
   1) Push notification waktu salat (walau halaman ditutup)
   2) Mode instal (PWA): aset statis dan beranda terakhir disimpan di perangkat supaya aplikasi tetap
      terbuka saat internet lemah atau mati. Jadwal salat sengaja tidak di-cache di sini: app.js sudah
      menyimpannya per hari di perangkat dan memuat ulang sendiri kalau tanggalnya berganti.
   Naikkan VERSI kalau daftar PRECACHE berubah. */
var VERSI = 'v2';
var CACHE_APP = 'ps-app-' + VERSI;     // aset statis (css, js, ikon, font)
var CACHE_HALAMAN = 'ps-halaman';      // beranda terakhir milik pengguna; dihapus saat keluar akun
var BATAS_JARINGAN = 4000;             // ms menunggu jaringan sebelum memakai salinan tersimpan

var PRECACHE = [
  '/offline',
  '/css/app.css', '/css/fitur.css',
  '/js/app.js', '/js/fitur.js', '/js/pwa.js',
  '/icons/icon-192.png', '/icons/icon-512.png'
];
var HOST_LUAR = ['cdn.jsdelivr.net', 'fonts.googleapis.com', 'fonts.gstatic.com'];

self.addEventListener('install', function (e) {
  e.waitUntil(
    caches.open(CACHE_APP).then(function (c) {
      // satu per satu: satu berkas gagal tidak menggagalkan semuanya
      return Promise.all(PRECACHE.map(function (u) { return c.add(u).catch(function () {}); }));
    }).then(function () { return self.skipWaiting(); })
  );
});

self.addEventListener('activate', function (e) {
  e.waitUntil(
    caches.keys().then(function (nama) {
      return Promise.all(nama.filter(function (n) {
        return n.indexOf('ps-app-') === 0 && n !== CACHE_APP;
      }).map(function (n) { return caches.delete(n); }));
    }).then(function () { return self.clients.claim(); })
  );
});

self.addEventListener('message', function (e) {
  if (e.data === 'bersihkan') e.waitUntil(caches.delete(CACHE_HALAMAN));
});

/* ---------- Strategi ---------- */
function simpan(nama, req, res, batas) {
  return caches.open(nama).then(function (c) {
    return c.put(req, res).then(function () {
      if (!batas) return;
      return c.keys().then(function (k) { if (k.length > batas) return c.delete(k[0]); });
    });
  }).catch(function () {});
}

// Jaringan dulu; kalau lambat/gagal pakai salinan terakhir.
// opsi.cache: nama cache; opsi.bolehSimpan(res): respons mana yang disimpan
function jaringanDulu(req, opsi) {
  return new Promise(function (selesaiFn) {
    var selesai = false;
    function kirim(r) { if (!selesai) { selesai = true; clearTimeout(timer); selesaiFn(r); } }
    function cadangan() {
      return caches.match(req, { cacheName: opsi.cache, ignoreSearch: !!opsi.abaikanQuery });
    }

    var timer = setTimeout(function () {
      cadangan().then(function (r) { if (r) kirim(r); }); // lambat: hanya pakai salinan kalau ada
    }, BATAS_JARINGAN);

    fetch(req).then(function (res) {
      if (opsi.bolehSimpan(res)) simpan(opsi.cache, req, res.clone(), opsi.batas);
      if (res.status >= 500) { // server error: lebih baik tampilkan salinan terakhir kalau ada
        cadangan().then(function (r) { kirim(r || res); });
        return;
      }
      kirim(res);
    }).catch(function () {
      cadangan().then(function (r) { return r || (opsi.cadanganAkhir && opsi.cadanganAkhir()); })
        .then(function (r) { kirim(r || Response.error()); });
    });
  });
}

// Font & ikon dari CDN (jarang berubah): pakai salinan langsung, perbarui diam-diam di belakang
function salinanDulu(req) {
  return caches.open(CACHE_APP).then(function (c) {
    return c.match(req).then(function (ada) {
      var ambil = fetch(req).then(function (res) {
        if (res && (res.ok || res.type === 'opaque')) c.put(req, res.clone());
        return res;
      }).catch(function () { return ada; });
      return ada || ambil;
    });
  });
}

self.addEventListener('fetch', function (e) {
  var req = e.request;
  if (req.method !== 'GET' || req.headers.has('range')) return;
  var url = new URL(req.url);

  if (url.origin === self.location.origin) {
    var path = url.pathname;

    // Beranda (hanya yang berhasil dimuat tanpa pengalihan, yaitu saat sudah login)
    if (req.mode === 'navigate') {
      if (path !== '/') return; // halaman lain (masuk, saran, admin) selalu dari jaringan
      e.respondWith(jaringanDulu(req, {
        cache: CACHE_HALAMAN,
        abaikanQuery: true,
        bolehSimpan: function (r) { return r.ok && r.type === 'basic' && !r.redirected; },
        cadanganAkhir: function () { return caches.match('/offline', { cacheName: CACHE_APP }); }
      }));
      return;
    }

    // CSS, JS, ikon: selalu versi terbaru kalau online (aman saat mengembangkan), salinan hanya untuk offline
    if (/^\/(css|js|icons)\//.test(path)) {
      e.respondWith(jaringanDulu(req, {
        cache: CACHE_APP,
        bolehSimpan: function (r) { return r.ok && r.type === 'basic'; }
      }));
    }
    return;
  }

  if (HOST_LUAR.indexOf(url.hostname) !== -1) e.respondWith(salinanDulu(req));
});

/* ---------- Push notification ---------- */
self.addEventListener('push', function (event) {
  var d = {};
  try { d = event.data ? event.data.json() : {}; } catch (e) {}
  var judul = d.judul || 'Pengingat Salat';
  event.waitUntil(self.registration.showNotification(judul, {
    body: d.isi || '',
    tag: d.tag || 'salat',
    renotify: true,
    icon: '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    data: { url: '/' }
  }));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
      for (var i = 0; i < list.length; i++) {
        if ('focus' in list[i]) return list[i].focus();
      }
      return self.clients.openWindow('/');
    })
  );
});
