/* Pengingat Salat — fitur tambahan: Beranda, Al-Quran, Tasbih, Asmaul Husna,
   Kiblat, Masjid terdekat, Tracker.
   Memakai window.PSX dari app.js. */
(function () {
  'use strict';

  var X = window.PSX;
  if (!X) return;
  var $ = X.$, esc = X.esc, store = X.store, hook = X.hook;

  var NAMA_HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

  function getJSON(url) {
    return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) {
      if (!r.ok) throw new Error('http ' + r.status);
      return r.json();
    });
  }

  /* =====================================================================
     MISI HARIAN (ibadah sunnah, disimpan di perangkat)
     ===================================================================== */
  var MISI = [
    ['dhuha', 'Salat Dhuha', 'ti-sun'],
    ['tahajud', 'Salat Tahajud', 'ti-moon-stars'],
    ['quran', 'Baca Al-Quran', 'ti-book-2'],
    ['dzikir', 'Dzikir / tasbih', 'ti-fingerprint'],
    ['sedekah', 'Sedekah', 'ti-heart-handshake'],
    ['puasa', 'Puasa sunnah', 'ti-moon']
  ];

  function misiHari(tgl) { return store.get('misi:' + tgl, {}); }
  function misiTandai(kunci, nilai) {
    var tgl = X.hariIni(), m = misiHari(tgl);
    if (nilai) m[kunci] = 1; else delete m[kunci];
    store.set('misi:' + tgl, m);
  }
  function misiJumlah(m) {
    return MISI.filter(function (x) { return m[x[0]]; }).length;
  }

  function gambarBeranda() {
    var m = misiHari(X.hariIni()), n = misiJumlah(m), t = MISI.length;
    $('misi-bar').style.width = Math.round(n / t * 100) + '%';
    $('misi-teks').textContent = n >= t
      ? 'Alhamdulillah, semua target ibadah hari ini lengkap!'
      : (n === 0 ? 'Ayo lengkapi target ibadahmu!' : n + ' dari ' + t + ' ibadah sunnah sudah dicatat.');
  }

  var PSCFG = window.PS || {};
  var ADMIN = !!(PSCFG.user && PSCFG.user.admin);
  var TILES = [
    ['quran', 'ti-book-2', 'Al-Quran'], ['jadwal', 'ti-clock', 'Jadwal'],
    ['tracker', 'ti-checklist', 'Tracker'], ['doa', 'ti-book', 'Doa'],
    ['dzikir', 'ti-sunset-2', 'Dzikir'], ['tasbih', 'ti-fingerprint', 'Tasbih'],
    ['asma', 'ti-sparkles', 'Asmaul Husna'], ['kiblat', 'ti-compass', 'Kiblat'],
    ['masjid', 'ti-map-pin', 'Masjid'], ['favorit', 'ti-heart', 'Favorit'],
    [ADMIN ? '/admin/saran' : '/saran', 'ti-message-2', 'Saran']
  ];
  $('aks').innerHTML = TILES.map(function (t) {
    var lencana = (t[2] === 'Saran' && PSCFG.saranBaru > 0)
      ? '<em class="lcn" aria-label="' + PSCFG.saranBaru + ' baru">' + (PSCFG.saranBaru > 99 ? '99+' : PSCFG.saranBaru) + '</em>' : '';
    return '<a class="aks-i" href="' + (t[0].charAt(0) === '/' ? t[0] : '#' + t[0]) + '"><span><i class="ti ' + t[1] + '" aria-hidden="true"></i>' + lencana + '</span>' + t[2] + '</a>';
  }).join('');

  hook.beranda = gambarBeranda;

  /* =====================================================================
     TRACKER (ibadah sunnah + grafik 7 hari)
     ===================================================================== */
  function gambarTracker() {
    var tgl = X.hariIni(), m = misiHari(tgl);
    $('tr-misi').innerHTML = MISI.map(function (x) {
      var on = !!m[x[0]];
      return '<button type="button" class="cb' + (on ? ' on' : '') + '" data-m="' + x[0] + '" aria-pressed="' + on + '">' +
        '<i class="ti ' + (on ? 'ti-circle-check-filled' : x[2]) + '" aria-hidden="true"></i>' + esc(x[1]) + '</button>';
    }).join('');

    var dasar = new Date(tgl + 'T12:00:00'), total = 0, bar = '';
    for (var i = 6; i >= 0; i--) {
      var d = new Date(dasar.getTime() - i * 86400000);
      var k = d.getFullYear() + '-' + X.p(d.getMonth() + 1) + '-' + X.p(d.getDate());
      var j = misiJumlah(misiHari(k));
      total += j;
      bar += '<div class="mb" title="' + j + ' dari ' + MISI.length + '"><div style="height:' + Math.max(Math.round(j / MISI.length * 100), 4) +
        '%"></div><span>' + NAMA_HARI[d.getDay()] + '</span></div>';
    }
    $('tr-mgg').innerHTML = bar;
    $('tr-info').textContent = 'Hari ini ' + misiJumlah(m) + '/' + MISI.length + ' · 7 hari terakhir: ' + total + '/' + (MISI.length * 7);
  }
  $('tr-misi').onclick = function (e) {
    var b = e.target.closest('[data-m]');
    if (!b) return;
    misiTandai(b.dataset.m, !b.classList.contains('on'));
    gambarTracker();
  };
  hook.tracker = gambarTracker;

  /* =====================================================================
     AL-QURAN (api.alquran.cloud: teks Uthmani, transliterasi, terjemah Indonesia, audio)
     ===================================================================== */
  var QAPI = 'https://api.alquran.cloud/v1';
  var surah = store.get('quran:list', null);
  var cacheSurah = {};
  var aud = null, audIdx = -1, putarSemua = false, ayatAktif = [];

  function namaSurah(s) { return s.englishName; }
  function tempat(s) { return s.revelationType === 'Meccan' ? 'MEKAH' : 'MADINAH'; }
  function arabSurah(s) { return String(s.name).replace(/^سُورَةُ\s*/, '').replace(/^سورة\s*/, ''); }

  function muatDaftarSurah() {
    if (surah) { gambarDaftarSurah(); }
    getJSON(QAPI + '/surah').then(function (j) {
      surah = j.data;
      store.set('quran:list', surah);
      gambarDaftarSurah();
    }).catch(function () {
      if (!surah) {
        $('qr-list').innerHTML = '<div class="kosong">Daftar surah belum bisa dimuat. Periksa koneksi internet.<br><button type="button" class="tbl" id="qr-ulang" style="margin-top:10px">Coba lagi</button></div>';
        $('qr-ulang').onclick = muatDaftarSurah;
      }
    });
  }

  function gambarDaftarSurah() {
    if (!surah) return;
    var q = $('qr-cari').value.toLowerCase().trim();
    var h = surah.filter(function (s) {
      return !q || (s.englishName + ' ' + s.englishNameTranslation + ' ' + s.number).toLowerCase().indexOf(q) !== -1;
    });
    $('qr-list').innerHTML = h.length ? h.map(function (s) {
      return '<button type="button" class="sr" data-s="' + s.number + '">' +
        '<span class="sr-no">' + s.number + '</span>' +
        '<span class="sr-t"><b>' + esc(namaSurah(s)) + '</b><small>' + tempat(s) + ' · ' + s.numberOfAyahs + ' AYAT</small></span>' +
        '<span class="sr-ar">' + esc(arabSurah(s)) + '</span></button>';
    }).join('') : '<div class="kosong">Surah tidak ditemukan.</div>';

    var akhir = store.get('quran:akhir', null);
    $('qr-lanjut').innerHTML = akhir && !q ? '<button type="button" class="k lanjut" data-s="' + akhir.n + '" data-a="' + akhir.a + '">' +
      '<i class="ti ti-bookmark" aria-hidden="true"></i><span><small>LANJUTKAN MEMBACA</small><b>' + esc(akhir.nama) + ' · ayat ' + akhir.a + '</b></span>' +
      '<i class="ti ti-chevron-right" aria-hidden="true"></i></button>' : '';
  }

  $('qr-cari').oninput = gambarDaftarSurah;
  $('qr-daftar').onclick = function (e) {
    var b = e.target.closest('[data-s]');
    if (b) bukaSurah(+b.dataset.s, b.dataset.a ? +b.dataset.a : 0);
  };

  function hentikanAudio() {
    putarSemua = false;
    if (aud) { try { aud.pause(); } catch (e) {} }
    audIdx = -1;
    ikonPutar();
  }

  function ambilSurah(n) {
    if (cacheSurah[n]) return Promise.resolve(cacheSurah[n]);
    return getJSON(QAPI + '/surah/' + n + '/editions/quran-uthmani,en.transliteration,id.indonesian,ar.alafasy').then(function (j) {
      var ar = j.data[0].ayahs, lat = j.data[1].ayahs, id = j.data[2].ayahs, au = j.data[3].ayahs;
      var buangBasmalah = n !== 1 && n !== 9;
      var hasil = ar.map(function (a, i) {
        var arab = a.text, latin = lat[i].text;
        if (buangBasmalah && i === 0) {
          // Teks API menyertakan basmalah di ayat pertama: dibuang (ditampilkan terpisah)
          var polos = arab.replace(/[ً-ٰٟۖ-ۭ]/g, '');
          if (polos.indexOf('بسم') === 0) arab = arab.split(' ').slice(4).join(' ');
          if (/^bismi/i.test(latin)) latin = latin.split(' ').slice(4).join(' ');
        }
        return { no: a.numberInSurah, arab: arab, latin: latin, arti: id[i].text, audio: au[i].audio };
      });
      cacheSurah[n] = { info: j.data[0], ayat: hasil };
      return cacheSurah[n];
    });
  }

  function bukaSurah(n, lompatKe) {
    hentikanAudio();
    var s = surah && surah[n - 1];
    $('qr-daftar').hidden = true;
    $('qr-detail').hidden = false;
    $('qr-detail').innerHTML = '<div class="kosong">Memuat surah…</div>';
    window.scrollTo(0, 0);
    ambilSurah(n).then(function (d) {
      gambarSurah(n, d, s, lompatKe);
      misiTandai('quran', true);
    }).catch(function () {
      $('qr-detail').innerHTML = '<div class="kosong">Surah belum bisa dimuat. Periksa koneksi internet.<br>' +
        '<button type="button" class="tbl" id="qr-ulang2" style="margin-top:10px">Coba lagi</button> ' +
        '<button type="button" class="tbl" id="qr-balik" style="margin-top:10px">Kembali</button></div>';
      $('qr-ulang2').onclick = function () { bukaSurah(n, lompatKe); };
      $('qr-balik').onclick = tutupSurah;
    });
  }

  function tutupSurah() {
    hentikanAudio();
    $('qr-detail').hidden = true;
    $('qr-daftar').hidden = false;
    gambarDaftarSurah();
    window.scrollTo(0, 0);
  }

  function gambarSurah(n, d, s, lompatKe) {
    ayatAktif = d.ayat;
    var nama = s ? namaSurah(s) : d.info.englishName;
    var tmp = s ? tempat(s) : (d.info.revelationType === 'Meccan' ? 'MEKAH' : 'MADINAH');
    var h = '<div class="qr-bar"><button type="button" class="ic" id="qr-kembali" aria-label="Kembali ke daftar surah"><i class="ti ti-chevron-left" aria-hidden="true"></i></button>' +
      '<div class="qr-judul"><b>' + esc(nama) + '</b><small>' + tmp + ' · ' + d.ayat.length + ' ayat</small></div>' +
      '<button type="button" class="ic qr-main" id="qr-semua" aria-label="Putar seluruh surah"><i class="ti ti-player-play-filled" aria-hidden="true"></i></button></div>';
    if (n !== 1 && n !== 9) {
      h += '<div class="k bsm"><p class="ar" style="margin:0 0 6px">بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</p><p class="sub" style="margin:0">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang.</p></div>';
    }
    h += d.ayat.map(function (a, i) {
      return '<div class="k ay" id="ay-' + a.no + '" data-i="' + i + '">' +
        '<div class="ay-h"><span class="ay-no">' + a.no + '</span><span class="ay-ab">' +
        '<button type="button" data-tandai="' + a.no + '" aria-label="Tandai terakhir dibaca ayat ' + a.no + '"><i class="ti ti-bookmark" aria-hidden="true"></i></button>' +
        '<button type="button" data-putar="' + i + '" aria-label="Putar ayat ' + a.no + '"><i class="ti ti-player-play" aria-hidden="true"></i></button></span></div>' +
        '<p class="ar">' + esc(a.arab) + '</p>' +
        '<p class="ay-lat">' + esc(a.latin) + '</p>' +
        '<p class="ay-arti">' + esc(a.arti) + '</p></div>';
    }).join('');
    $('qr-detail').innerHTML = h;
    $('qr-detail').dataset.n = n;
    $('qr-detail').dataset.nama = nama;
    $('qr-kembali').onclick = tutupSurah;
    $('qr-semua').onclick = function () {
      if (putarSemua) { hentikanAudio(); } else { putarSemua = true; mainAyat(0); }
    };
    if (lompatKe) {
      var el = $('ay-' + lompatKe);
      if (el) setTimeout(function () { el.scrollIntoView({ block: 'center' }); }, 50);
    }
  }

  function ikonPutar() {
    Array.prototype.forEach.call(document.querySelectorAll('#qr-detail [data-putar]'), function (b) {
      var aktif = +b.dataset.putar === audIdx;
      b.classList.toggle('on', aktif);
      b.firstChild.className = 'ti ' + (aktif ? 'ti-player-pause' : 'ti-player-play');
      b.closest('.ay').classList.toggle('sedang', aktif);
    });
    var sm = $('qr-semua');
    if (sm) sm.firstChild.className = 'ti ' + (putarSemua ? 'ti-player-pause-filled' : 'ti-player-play-filled');
  }

  function mainAyat(i) {
    if (i < 0 || i >= ayatAktif.length) { hentikanAudio(); return; }
    if (!aud) {
      aud = new Audio();
      aud.onended = function () {
        if (putarSemua) mainAyat(audIdx + 1); else { audIdx = -1; ikonPutar(); }
      };
      aud.onerror = function () { putarSemua = false; audIdx = -1; ikonPutar(); };
    }
    audIdx = i;
    aud.src = ayatAktif[i].audio;
    var pr = aud.play();
    if (pr && pr.catch) pr.catch(function () { putarSemua = false; audIdx = -1; ikonPutar(); });
    ikonPutar();
    var el = document.querySelector('#qr-detail [data-i="' + i + '"]');
    if (el && putarSemua) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
  }

  $('qr-detail').onclick = function (e) {
    var p = e.target.closest('[data-putar]'), t = e.target.closest('[data-tandai]');
    if (p) {
      var i = +p.dataset.putar;
      if (audIdx === i) { hentikanAudio(); } else { putarSemua = false; mainAyat(i); }
    }
    if (t) {
      var d = $('qr-detail');
      store.set('quran:akhir', { n: +d.dataset.n, a: +t.dataset.tandai, nama: d.dataset.nama });
      t.classList.add('on');
      t.firstChild.className = 'ti ti-bookmark-filled';
    }
  };

  hook.quran = function () {
    if (!surah || $('qr-list').querySelector('.kosong')) muatDaftarSurah(); else gambarDaftarSurah();
  };

  /* =====================================================================
     TASBIH DIGITAL
     ===================================================================== */
  var DZIKIR = [
    { n: 'Subhanallah', ar: 'سُبْحَانَ ٱللَّهِ', t: 33, a: 'Maha Suci Allah dari segala kekurangan.' },
    { n: 'Alhamdulillah', ar: 'ٱلْحَمْدُ لِلَّهِ', t: 33, a: 'Segala puji bagi Allah.' },
    { n: 'Allahu Akbar', ar: 'ٱللَّهُ أَكْبَرُ', t: 33, a: 'Allah Maha Besar.' },
    { n: 'Laa ilaha illallah', ar: 'لَا إِلَٰهَ إِلَّا ٱللَّهُ', t: 100, a: 'Tiada tuhan selain Allah.' },
    { n: 'Astaghfirullah', ar: 'أَسْتَغْفِرُ ٱللَّهَ', t: 100, a: 'Aku memohon ampun kepada Allah.' },
    { n: 'Shalawat', ar: 'ٱللَّهُمَّ صَلِّ عَلَىٰ مُحَمَّدٍ', t: 100, a: 'Ya Allah, limpahkanlah shalawat kepada Nabi Muhammad.' }
  ];
  var tb = store.get('tasbih', { i: 0, c: {} });
  if (!tb || typeof tb.i !== 'number' || !tb.c) tb = { i: 0, c: {} };

  function gambarTasbih(animasi) {
    var d = DZIKIR[tb.i], c = tb.c[tb.i] || 0;
    $('tb-pilih').innerHTML = DZIKIR.map(function (x, i) {
      return '<span class="ch' + (i === tb.i ? ' a' : '') + '" data-d="' + i + '" tabindex="0" role="tab" aria-selected="' + (i === tb.i) + '">' + esc(x.n) + '</span>';
    }).join('');
    $('tb-arab').textContent = d.ar;
    $('tb-latin').textContent = d.n;
    $('tb-target').textContent = d.t;
    $('tb-arti').textContent = d.a;
    $('tb-n').textContent = c;
    $('tb-n').classList.toggle('capai', c >= d.t);
    $('tb-ring').style.setProperty('--p', Math.min(100, c / d.t * 100));
    if (animasi) {
      $('tb-tap').classList.remove('pop');
      void $('tb-tap').offsetWidth;
      $('tb-tap').classList.add('pop');
    }
  }
  function simpanTasbih() { store.set('tasbih', tb); }

  $('tb-tap').onclick = function () {
    var d = DZIKIR[tb.i];
    tb.c[tb.i] = (tb.c[tb.i] || 0) + 1;
    simpanTasbih();
    gambarTasbih(true);
    try { if (navigator.vibrate) navigator.vibrate(tb.c[tb.i] === d.t ? [60, 40, 60] : 12); } catch (e) {}
    if (tb.c[tb.i] === d.t) { confetti(); misiTandai('dzikir', true); }
  };
  $('tb-reset').onclick = function () { tb.c[tb.i] = 0; simpanTasbih(); gambarTasbih(); };
  function pilihDzikir(e) {
    var c = e.target.closest('[data-d]');
    if (!c) return;
    if (e.type === 'keydown') { if (e.key !== 'Enter' && e.key !== ' ') return; e.preventDefault(); }
    tb.i = +c.dataset.d; simpanTasbih(); gambarTasbih();
  }
  $('tb-pilih').onclick = pilihDzikir;
  $('tb-pilih').onkeydown = pilihDzikir;
  hook.tasbih = function () { gambarTasbih(); };

  // Confetti sederhana (kanvas layar penuh, hilang sendiri)
  function confetti() {
    var cv = $('cf'), ctx = cv.getContext && cv.getContext('2d');
    if (!ctx) return;
    if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    cv.width = innerWidth; cv.height = innerHeight; cv.style.display = 'block';
    var warna = ['#e0b64a', '#22c55e', '#f1c65a', '#60a5fa', '#f472b6', '#ffffff'];
    var bit = [];
    for (var i = 0; i < 90; i++) {
      bit.push({
        x: innerWidth / 2 + (Math.random() - .5) * 80, y: innerHeight * .55,
        vx: (Math.random() - .5) * 12, vy: -Math.random() * 13 - 4,
        r: Math.random() * 4 + 2, c: warna[i % warna.length], a: 1
      });
    }
    var t0 = performance.now();
    (function gerak(t) {
      ctx.clearRect(0, 0, cv.width, cv.height);
      bit.forEach(function (b) {
        b.x += b.vx; b.y += b.vy; b.vy += .35; b.vx *= .99; b.a = Math.max(0, 1 - (t - t0) / 1800);
        ctx.globalAlpha = b.a; ctx.fillStyle = b.c;
        ctx.fillRect(b.x, b.y, b.r, b.r * 1.6);
      });
      if (t - t0 < 1800) requestAnimationFrame(gerak); else { ctx.clearRect(0, 0, cv.width, cv.height); cv.style.display = 'none'; }
    })(t0);
  }

  /* =====================================================================
     DZIKIR PAGI & PETANG
     Isi dari database (dikelola admin). Hitungan tersimpan per hari di perangkat.
     ===================================================================== */
  var dzSemua = (PSCFG.dzikir || []).map(function (d) {
    d.id = +d.id; d.jumlah = Math.max(1, +d.jumlah || 1); // jaga-jaga kalau database mengirim angka sebagai teks
    return d;
  });
  var dzWaktu = new Date().getHours() < 12 ? 'pagi' : 'petang';

  function dzDaftar() {
    return dzSemua.filter(function (d) { return d.waktu === 'keduanya' || d.waktu === dzWaktu; });
  }
  function dzKunci() { return 'dzikir:' + X.hariIni() + ':' + dzWaktu; }
  function dzHitung() {
    var h = store.get(dzKunci(), {});
    return h && typeof h === 'object' ? h : {};
  }
  function dzCari(id) {
    for (var i = 0; i < dzSemua.length; i++) if (dzSemua[i].id === id) return dzSemua[i];
    return null;
  }
  function dzIsiTombol(el, d, c) {
    var sel = c >= d.jumlah;
    el.classList.toggle('sel', sel);
    var tombol = el.querySelector('.dz-btn');
    tombol.disabled = sel;
    tombol.querySelector('.tb-ring').style.setProperty('--p', Math.min(100, c / d.jumlah * 100));
    tombol.querySelector('b').innerHTML = sel ? '<i class="ti ti-check" aria-hidden="true"></i>' : c;
    tombol.querySelector('small').textContent = sel ? 'SELESAI' : '/ ' + d.jumlah;
    tombol.setAttribute('aria-label', sel ? d.judul + ': selesai' : 'Tambah hitungan ' + d.judul + ', sekarang ' + c + ' dari ' + d.jumlah);
    el.querySelector('.dz-ulang').hidden = c === 0;
  }
  function dzProgres() {
    var daftar = dzDaftar(), h = dzHitung();
    var selesai = daftar.filter(function (d) { return (h[d.id] || 0) >= d.jumlah; }).length;
    var nama = dzWaktu === 'pagi' ? 'pagi' : 'petang';
    $('dz-bar').style.width = (daftar.length ? Math.round(selesai / daftar.length * 100) : 0) + '%';
    $('dz-info').textContent = !daftar.length ? 'Belum ada dzikir ' + nama + '.'
      : (selesai >= daftar.length ? 'Alhamdulillah, dzikir ' + nama + ' selesai.' : selesai + ' dari ' + daftar.length + ' bacaan selesai');
    return selesai >= daftar.length && daftar.length > 0;
  }
  function dzGambar() {
    var daftar = dzDaftar(), h = dzHitung();
    Array.prototype.forEach.call(document.querySelectorAll('.dz-tab button'), function (b) {
      var on = b.dataset.w === dzWaktu;
      b.classList.toggle('a', on);
      b.setAttribute('aria-selected', on);
    });
    $('dz-list').innerHTML = daftar.length ? daftar.map(function (d) {
      return '<article class="k dz" data-id="' + d.id + '">' +
        '<div class="baris" style="margin:0 0 6px"><b style="font-size:14px">' + esc(d.judul) + '</b>' +
        '<span class="ch" style="cursor:default;margin:0">' + d.jumlah + '×</span></div>' +
        '<p class="dz-ar" dir="rtl" lang="ar">' + esc(d.arab) + '</p>' +
        '<p class="dz-lt">' + esc(d.latin) + '</p>' +
        '<p class="sub" style="margin:6px 0 0">' + esc(d.arti) + '</p>' +
        (d.sumber ? '<p class="sub dz-sm">' + esc(d.sumber) + '</p>' : '') +
        '<div class="dz-aksi">' +
          '<button type="button" class="tbl dz-ulang" data-ulang="' + d.id + '" hidden><i class="ti ti-refresh" aria-hidden="true"></i>Ulang</button>' +
          '<button type="button" class="dz-btn" data-tap="' + d.id + '"><span class="tb-ring"></span><b>0</b><small></small></button>' +
        '</div></article>';
    }).join('') : '<div class="kosong">Belum ada dzikir untuk waktu ini.' + (ADMIN ? ' Tambahkan dari menu admin.' : '') + '</div>';
    Array.prototype.forEach.call($('dz-list').querySelectorAll('.dz'), function (el) {
      var d = dzCari(+el.dataset.id);
      if (d) dzIsiTombol(el, d, h[d.id] || 0);
    });
    dzProgres();
  }

  $('dz-list').onclick = function (e) {
    var tap = e.target.closest('[data-tap]'), ulang = e.target.closest('[data-ulang]');
    var id = +(tap ? tap.dataset.tap : ulang ? ulang.dataset.ulang : 0);
    var d = id && dzCari(id);
    if (!d) return;
    var el = tap ? tap.closest('.dz') : ulang.closest('.dz');
    var h = dzHitung();

    if (ulang) { delete h[id]; store.set(dzKunci(), h); dzIsiTombol(el, d, 0); dzProgres(); return; }

    var c = (h[id] || 0);
    if (c >= d.jumlah) return;
    h[id] = ++c;
    store.set(dzKunci(), h);
    dzIsiTombol(el, d, c);
    tap.classList.remove('pop'); void tap.offsetWidth; tap.classList.add('pop');
    try { if (navigator.vibrate) navigator.vibrate(c === d.jumlah ? [60, 40, 60] : 12); } catch (err) {}

    var semua = dzProgres();
    if (c === d.jumlah) {
      if (semua) { confetti(); misiTandai('dzikir', true); return; }
      // lanjut ke bacaan berikutnya yang belum selesai
      var berikut = el.nextElementSibling;
      while (berikut && berikut.classList.contains('sel')) berikut = berikut.nextElementSibling;
      if (berikut && berikut.scrollIntoView) {
        var pelan = !(window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches);
        setTimeout(function () { berikut.scrollIntoView({ behavior: pelan ? 'smooth' : 'auto', block: 'start' }); }, 450);
      }
    }
  };
  $('dz-reset').onclick = function () {
    if (!confirm('Ulangi hitungan dzikir ' + dzWaktu + ' hari ini dari awal?')) return;
    store.set(dzKunci(), {});
    dzGambar();
  };
  Array.prototype.forEach.call(document.querySelectorAll('.dz-tab button'), function (b) {
    b.onclick = function () { dzWaktu = b.dataset.w; dzGambar(); window.scrollTo(0, 0); };
  });
  hook.dzikir = dzGambar;

  /* =====================================================================
     ASMAUL HUSNA
     ===================================================================== */
  function gambarAsma() {
    var q = $('as-cari').value.toLowerCase().trim();
    // Isi berasal dari database (dikelola admin), dikirim lewat window.PS.asma
    var h = (PSCFG.asma || []).filter(function (a) {
      return !q || (a.latin + ' ' + a.arti + ' ' + a.nomor).toLowerCase().indexOf(q) !== -1;
    });
    $('as-list').innerHTML = h.length ? '<div class="asg">' + h.map(function (a) {
      return '<div class="as"><span class="as-no">' + a.nomor + '</span><p class="as-ar">' + esc(a.arab) + '</p><b>' + esc(a.latin) + '</b><small>' + esc(a.arti) + '</small></div>';
    }).join('') + '</div>' : '<div class="kosong">' + (q ? 'Tidak ditemukan.' : 'Belum ada data.') + '</div>';
  }
  $('as-cari').oninput = gambarAsma;
  hook.asma = gambarAsma;

  /* =====================================================================
     ARAH KIBLAT
     ===================================================================== */
  var KAABAH = { lat: 21.4225, lon: 39.8262 };
  var qibla = 0, kompasAktif = false, tadiSejajar = false;

  function hitungKiblat(lat, lon) {
    var r = Math.PI / 180, dl = (KAABAH.lon - lon) * r, f1 = lat * r, f2 = KAABAH.lat * r;
    var y = Math.sin(dl) * Math.cos(f2);
    var x = Math.cos(f1) * Math.sin(f2) - Math.sin(f1) * Math.cos(f2) * Math.cos(dl);
    return (Math.atan2(y, x) / r + 360) % 360;
  }
  function mataAngin(d) {
    return ['utara', 'timur laut', 'timur', 'tenggara', 'selatan', 'barat daya', 'barat', 'barat laut'][Math.round(d / 45) % 8];
  }
  function putarKompas(head) {
    $('kb-dial').style.transform = 'rotate(' + (-head) + 'deg)';
    var selisih = Math.abs(((qibla - head) % 360 + 540) % 360 - 180);
    var sejajar = selisih <= 4;
    $('kb-dial').classList.toggle('lurus', sejajar);
    if (sejajar && !tadiSejajar) { try { if (navigator.vibrate) navigator.vibrate(40); } catch (e) {} }
    tadiSejajar = sejajar;
    $('kb-ket').textContent = sejajar ? 'Kamu sudah menghadap kiblat.' : 'Putar perangkatmu sampai ikon masjid berada di atas.';
  }
  function gambarKiblat() {
    var l = X.lokasi();
    qibla = hitungKiblat(l.lat, l.lon);
    $('kb-lok').textContent = 'Dari ' + l.nama + ' ke Ka\'bah.';
    $('kb-der').textContent = Math.round(qibla) + '° ' + mataAngin(qibla);
    $('kb-mark').style.transform = 'rotate(' + qibla + 'deg)';
    if (!kompasAktif) $('kb-dial').style.transform = 'rotate(0deg)';
  }
  function dengarKompas(e) {
    var head = null;
    if (typeof e.webkitCompassHeading === 'number') head = e.webkitCompassHeading;
    else if ((e.absolute || e.type === 'deviceorientationabsolute') && typeof e.alpha === 'number') head = 360 - e.alpha;
    if (head === null) return;
    kompasAktif = true;
    putarKompas(head);
  }
  $('kb-aktif').onclick = function () {
    if (!window.DeviceOrientationEvent) { $('kb-ket').textContent = 'Perangkat ini tidak punya sensor kompas. Pakai angka derajat di atas dari arah utara.'; return; }
    var pasang = function () {
      window.addEventListener('deviceorientationabsolute', dengarKompas, true);
      window.addEventListener('deviceorientation', dengarKompas, true);
      $('kb-ket').textContent = 'Menunggu sensor kompas…';
      setTimeout(function () {
        if (!kompasAktif) $('kb-ket').textContent = 'Sensor kompas tidak terbaca (biasanya hanya ada di HP, dan butuh HTTPS). Pakai angka derajat di atas dari arah utara.';
      }, 2500);
    };
    if (typeof DeviceOrientationEvent.requestPermission === 'function') {
      DeviceOrientationEvent.requestPermission().then(function (r) {
        if (r === 'granted') pasang(); else $('kb-ket').textContent = 'Izin sensor ditolak.';
      }).catch(function () { $('kb-ket').textContent = 'Izin sensor tidak bisa diminta.'; });
    } else { pasang(); }
  };
  hook.kiblat = gambarKiblat;

  /* =====================================================================
     MASJID TERDEKAT (OpenStreetMap lewat Overpass)
     ===================================================================== */
  var msPosisi = null, msMuat = false;

  function jarakKm(a, b, c, d) {
    var r = Math.PI / 180, dl = (d - b) * r, dp = (c - a) * r;
    var h = Math.sin(dp / 2) * Math.sin(dp / 2) + Math.cos(a * r) * Math.cos(c * r) * Math.sin(dl / 2) * Math.sin(dl / 2);
    return 6371 * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
  }
  function cariMasjid() {
    var l = msPosisi || X.lokasi();
    $('ms-lok').textContent = 'Pencarian di sekitar: ' + (l.nama || 'lokasimu') + ' (radius 3 km).';
    $('ms-peta').href = 'https://www.google.com/maps/search/masjid/@' + l.lat + ',' + l.lon + ',15z';
    if (msMuat) return;
    msMuat = true;
    $('ms-list').innerHTML = '<div class="kosong">Mencari masjid…</div>';
    var q = '[out:json][timeout:20];(node(around:3000,' + l.lat + ',' + l.lon + ')["amenity"="place_of_worship"]["religion"="muslim"];' +
      'way(around:3000,' + l.lat + ',' + l.lon + ')["amenity"="place_of_worship"]["religion"="muslim"];);out center 60;';
    getJSON('https://overpass-api.de/api/interpreter?data=' + encodeURIComponent(q)).then(function (j) {
      var h = j.elements.map(function (e) {
        var la = e.lat || (e.center && e.center.lat), lo = e.lon || (e.center && e.center.lon);
        return { nama: (e.tags && (e.tags.name || e.tags['name:id'])) || 'Masjid / musala (tanpa nama)', lat: la, lon: lo, km: jarakKm(l.lat, l.lon, la, lo) };
      }).filter(function (m) { return m.lat && m.lon; }).sort(function (a, b) { return a.km - b.km; }).slice(0, 15);
      $('ms-list').innerHTML = h.length ? h.map(function (m) {
        return '<div class="k ms"><i class="ti ti-building-mosque" aria-hidden="true"></i><div style="flex:1;min-width:0"><b>' + esc(m.nama) + '</b>' +
          '<small>' + (m.km < 1 ? Math.round(m.km * 1000) + ' m' : m.km.toFixed(1) + ' km') + '</small></div>' +
          '<a class="tbl" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=' + m.lat + ',' + m.lon + '">Rute</a></div>';
      }).join('') : '<div class="kosong">Tidak ada data masjid di radius 3 km. Coba tombol Google Maps di atas.</div>';
    }).catch(function () {
      $('ms-list').innerHTML = '<div class="kosong">Daftar masjid belum bisa dimuat. Pakai tombol Google Maps di atas.</div>';
    }).finally(function () { msMuat = false; });
  }
  $('ms-gps').onclick = function () {
    if (!navigator.geolocation) { $('ms-lok').textContent = 'Peramban tidak mendukung lokasi.'; return; }
    $('ms-lok').textContent = 'Mencari lokasi…';
    navigator.geolocation.getCurrentPosition(function (pos) {
      msPosisi = { nama: 'Lokasi saya', lat: +pos.coords.latitude.toFixed(5), lon: +pos.coords.longitude.toFixed(5) };
      cariMasjid();
    }, function (err) {
      $('ms-lok').textContent = err.code === 1 ? 'Izin lokasi ditolak. Memakai kota pilihanmu.' : 'Lokasi tidak ditemukan.';
      msPosisi = null; cariMasjid();
    }, { timeout: 10000, maximumAge: 600000 });
  };
  hook.masjid = function () { cariMasjid(); };

  /* ---------- Mulai ---------- */
  // fitur.js dimuat setelah app.js: jalankan hook halaman yang sedang terbuka
  X.ulangiHalaman();
  setInterval(function () { if (!$('pg-beranda').hidden) gambarBeranda(); }, 60000);
})();
