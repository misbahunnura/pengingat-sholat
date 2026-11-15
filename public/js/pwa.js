/* Mode instal (PWA): daftarkan service worker, tawarkan "Pasang di layar utama",
   dan bersihkan salinan halaman saat keluar akun. */
(function () {
  'use strict';

  var $ = function (id) { return document.getElementById(id); };

  /* ---------- Service worker ---------- */
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
  }

  /* ---------- Keluar akun: hapus salinan beranda/jadwal di perangkat (penting di HP bersama) ---------- */
  Array.prototype.forEach.call(document.querySelectorAll('form[action$="/keluar"]'), function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.caches || f.dataset.bersih) return;
      e.preventDefault();
      var lanjut = function () { f.dataset.bersih = '1'; f.submit(); };
      caches.delete('ps-halaman').then(lanjut, lanjut);
    });
  });

  /* ---------- Tombol pasang (hanya ada di beranda) ---------- */
  var kartu = $('pasang');
  if (!kartu) return;

  var KUNCI = 'ps:pasang-tutup';
  var sudahTerpasang = (window.matchMedia && matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
  if (sudahTerpasang) return;

  try {
    var t = +localStorage.getItem(KUNCI);
    if (t && Date.now() - t < 7 * 864e5) return; // ditutup < 7 hari lalu
  } catch (e) {}

  var tunda = null;
  var tombol = $('pasang-btn'), teks = $('pasang-teks');

  $('pasang-x').onclick = function () {
    kartu.hidden = true;
    try { localStorage.setItem(KUNCI, String(Date.now())); } catch (e) {}
  };

  // Android/Chrome/Edge: peramban memberi tahu kalau aplikasi bisa dipasang
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    tunda = e;
    tombol.hidden = false;
    kartu.hidden = false;
  });

  tombol.onclick = function () {
    if (!tunda) return;
    tunda.prompt();
    tunda.userChoice.then(function () { tunda = null; kartu.hidden = true; });
  };

  window.addEventListener('appinstalled', function () { kartu.hidden = true; });

  // iPhone/iPad (Safari) tidak punya tombol pasang otomatis: tampilkan petunjuk
  var ios = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  if (ios) {
    teks.textContent = 'Ketuk tombol Bagikan di Safari, lalu pilih "Tambah ke Layar Utama".';
    kartu.hidden = false;
  }
})();
