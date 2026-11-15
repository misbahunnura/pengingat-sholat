/* Pengingat Salat — logika halaman */
(function () {
  'use strict';

  var CFG = window.PS;
  var $ = function (id) { return document.getElementById(id); };
  var p = function (n) { return String(n).padStart(2, '0'); };
  var html = document.documentElement;

  /* ---------- Penyimpanan lokal ---------- */
  var store = {
    get: function (k, d) {
      try { var v = localStorage.getItem('ps:' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; }
    },
    set: function (k, v) {
      try { localStorage.setItem('ps:' + k, JSON.stringify(v)); } catch (e) {}
    }
  };

  var esc = function (s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  };

  /* ---------- Panggilan ke server (JSON + CSRF) ---------- */
  var USER = CFG.user || null;
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  function api(metode, url, body) {
    var opsi = {
      method: metode,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (body !== undefined) {
      opsi.headers['Content-Type'] = 'application/json';
      opsi.body = JSON.stringify(body);
    }
    return fetch(url, opsi).then(function (r) {
      if (r.status === 401 || r.status === 419) { throw new Error('sesi'); }
      if (!r.ok) throw new Error('http ' + r.status);
      return r.json();
    });
  }

  /* ---------- Tema ---------- */
  function pasangIkonTema() {
    $('ik').className = 'ti ' + (html.dataset.t === 'dark' ? 'ti-sun' : 'ti-moon');
  }
  $('tg').onclick = function () {
    html.dataset.t = html.dataset.t === 'dark' ? 'light' : 'dark';
    store.set('tema', html.dataset.t);
    pasangIkonTema();
  };
  pasangIkonTema();

  /* ---------- Waktu menurut zona kota ---------- */
  function bagian(tz, d) {
    var o = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: tz, hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(d || new Date()).forEach(function (x) { o[x.type] = x.value; });
    return o;
  }
  function detikHariIni(tz) {
    var o = bagian(tz);
    return (+o.hour % 24) * 3600 + (+o.minute) * 60 + (+o.second);
  }
  function kunciTanggal(tz) {
    var o = bagian(tz);
    return o.year + '-' + o.month + '-' + o.day;
  }
  function tanggalPanjang(tz) {
    return new Intl.DateTimeFormat('id-ID', {
      timeZone: tz, weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    }).format(new Date());
  }
  function hariJumat(tz) {
    return new Intl.DateTimeFormat('en-US', { timeZone: tz, weekday: 'long' }).format(new Date()) === 'Friday';
  }
  function jamKeDetik(s) {
    var a = s.split(':');
    return (+a[0]) * 3600 + (+a[1]) * 60;
  }
  function fmt(s) {
    s = ((s % 86400) + 86400) % 86400;
    return p(Math.floor(s / 3600)) + ':' + p(Math.floor(s % 3600 / 60));
  }

  /* ---------- Lokasi & jadwal ---------- */
  var zonaPerangkat = (function () {
    try { return Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Jakarta'; } catch (e) { return 'Asia/Jakarta'; }
  })();

  var bawaan = CFG.kota.filter(function (k) { return k.nama === CFG.bawaan; })[0] || CFG.kota[0];
  // Pengguna login: lokasi dari akun (ikut berpindah antar perangkat). Selain itu: dari peramban.
  var lokasiLokal = store.get('lokasi', null);
  var lokasi = (USER && USER.lokasi) || lokasiLokal || bawaan;
  if (USER && !USER.lokasi && lokasiLokal) { simpanLokasiServer(lokasiLokal); }

  function simpanLokasiServer(l) {
    if (!USER) return;
    api('PUT', '/api/lokasi', { nama: l.nama, lat: l.lat, lon: l.lon, tz: l.tz }).catch(function () {});
  }
  var data = null;        // jawaban terakhir dari /api/jadwal
  var memuat = false;

  function kunciLokasi(l) { return l.lat.toFixed(3) + ',' + l.lon.toFixed(3) + ',' + l.tz; }

  function tampilError(pesan) {
    $('errt').textContent = pesan;
    $('err').hidden = false;
  }
  function sembunyiError() { $('err').hidden = true; }

  function muatJadwal() {
    if (memuat) return;
    memuat = true;
    var l = lokasi;
    var kt = kunciTanggal(l.tz);
    var kcache = 'jadwal:' + kunciLokasi(l) + ':' + kt;

    var q = new URLSearchParams({ lat: l.lat, lon: l.lon, tz: l.tz });
    fetch(CFG.urlJadwal + '?' + q.toString(), { headers: { 'Accept': 'application/json' } })
      .then(function (r) {
        if (!r.ok) throw new Error('http ' + r.status);
        return r.json();
      })
      .then(function (j) {
        if (l !== lokasi) return; // pengguna sudah pindah kota
        data = j;
        store.set(kcache, j);
        sembunyiError();
        render();
      })
      .catch(function () {
        if (l !== lokasi) return;
        var simpan = store.get(kcache, null);
        if (simpan) {
          data = simpan;
          tampilError('Tidak ada koneksi. Memakai jadwal yang tersimpan di perangkat.');
          render();
        } else {
          data = null;
          $('info').textContent = l.nama;
          tampilError('Jadwal salat belum bisa dimuat. Periksa koneksi internet lalu coba lagi.');
        }
      })
      .finally(function () { memuat = false; });
  }
  $('ulang').onclick = function () { sembunyiError(); muatJadwal(); };

  /* ---------- Tampilan jadwal ---------- */
  var ULANG = [
    ['Subuh', 'ti-moon-stars'], ['Terbit', 'ti-sunrise'], ['Zuhur', 'ti-sun-high'],
    ['Asar', 'ti-sun'], ['Magrib', 'ti-sunset-2'], ['Isya', 'ti-moon']
  ];

  function daftarWaktu() {
    var j = data.jadwal;
    var jumat = hariJumat(data.tz);
    return ULANG.map(function (u, i) {
      var nama = (u[0] === 'Zuhur' && jumat) ? 'Jumat' : u[0];
      return [nama, jamKeDetik(j[u[0]]), u[1], u[0]];
    });
  }

  function render() {
    if (!data) return;

    // Ganti hari: ambil jadwal baru
    if (data.tanggal !== kunciTanggal(data.tz) && !memuat) { muatJadwal(); }

    var L = daftarWaktu();
    var n = detikHariIni(data.tz);
    var i = -1, k;
    for (k = 0; k < L.length; k++) { if (L[k][1] > n) { i = k; break; } }
    var sisa, prev;
    if (i < 0) {                       // sudah lewat Isya → Subuh berikutnya (perkiraan)
      i = 0;
      sisa = 86400 - n + L[0][1];
      prev = L[5][1];
    } else {
      sisa = L[i][1] - n;
      prev = i === 0 ? L[5][1] - 86400 : L[i - 1][1];
    }
    var tujuan = L[i][1] > n ? L[i][1] : L[i][1] + 86400;
    var tot = tujuan - prev;
    var pct = Math.max(0, Math.min(100, Math.round((1 - sisa / tot) * 100)));

    $('info').textContent = lokasi.nama + ' · ' + tanggalPanjang(data.tz) + ' · ' + data.hijri;
    $('nama').textContent = L[i][0] + ' · ' + fmt(L[i][1]);
    $('hitung').textContent = p(Math.floor(sisa / 3600)) + ':' + p(Math.floor(sisa % 3600 / 60)) + ':' + p(sisa % 60);
    $('pb').style.width = pct + '%';
    $('plabel').textContent = pct + '% menuju ' + L[i][0];
    $('imsak').textContent = 'Imsak ' + data.jadwal.Imsak;

    // Kartu "Salat berikutnya" di Beranda
    $('bn-nama').textContent = L[i][0] + ' · ' + fmt(L[i][1]);
    $('bn-sisa').textContent = (sisa >= 3600 ? Math.floor(sisa / 3600) + ' jam ' : '') + Math.floor(sisa % 3600 / 60) + ' menit lagi · ' + lokasi.nama;

    $('grid').innerHTML = L.map(function (x, idx) {
      var cls = idx === i ? ' n' : (x[1] < n ? ' x' : '');
      return '<div class="c' + cls + '"><i class="ti ' + x[2] + '" aria-hidden="true"></i><p>' + x[0] + '</p><b>' + fmt(x[1]) + '</b></div>';
    }).join('');

    cekPengingat(L, n);
    if (USER && catatanTanggal !== data.tanggal && !catatanMemuat) muatCatatan();
  }

  /* ---------- Catatan salat (hanya pengguna login) ---------- */
  var WAKTU_WAJIB = ['Subuh', 'Zuhur', 'Asar', 'Magrib', 'Isya'];
  var catatanTanggal = null, catatanMemuat = false, catatan = null;

  function muatCatatan() {
    if (!USER || !data) return;
    var tgl = data.tanggal;
    catatanMemuat = true;
    api('GET', '/api/catatan?tanggal=' + encodeURIComponent(tgl)).then(function (j) {
      catatan = j;
      catatanTanggal = tgl;
      gambarCatatan();
    }).catch(function () {
      catatanTanggal = tgl; // jangan mengulang tiap detik
      $('catinfo').textContent = 'Catatan belum bisa dimuat. Muat ulang halaman untuk mencoba lagi.';
    }).finally(function () { catatanMemuat = false; });
  }

  function gambarCatatan() {
    if (!catatan) return;
    var tandai = {};
    catatan.hari.forEach(function (w) { tandai[w] = true; });
    var jumat = data && hariJumat(data.tz);
    $('catgrid').innerHTML = WAKTU_WAJIB.map(function (w) {
      var nama = (w === 'Zuhur' && jumat) ? 'Jumat' : w;
      var on = !!tandai[w];
      return '<button type="button" class="cb' + (on ? ' on' : '') + '" data-w="' + w + '" aria-pressed="' + on + '">' +
        '<i class="ti ' + (on ? 'ti-circle-check-filled' : 'ti-circle') + '" aria-hidden="true"></i>' + nama + '</button>';
    }).join('');

    $('catmgg').innerHTML = catatan.minggu.map(function (h) {
      var hh = Math.round(h.jumlah / 5 * 100);
      var hari = new Date(h.tanggal + 'T12:00:00').toLocaleDateString('id-ID', { weekday: 'short' });
      return '<div class="mb" title="' + h.jumlah + ' dari 5"><div style="height:' + Math.max(hh, 4) + '%"></div><span>' + esc(hari) + '</span></div>';
    }).join('');

    $('catinfo').textContent = catatan.hari.length + ' dari 5 salat hari ini · 7 hari: ' + catatan.total7 + '/35 · ' +
      'Rangkaian hari lengkap: ' + catatan.rangkaian;
  }

  // Hanya ada kalau sudah login (tamu tidak punya elemen ini)
  if ($('catgrid')) $('catgrid').onclick = function (e) {
    var b = e.target.closest('[data-w]');
    if (!b || !USER || !catatan) return;
    var w = b.dataset.w, ada = catatan.hari.indexOf(w) !== -1;
    // Perbarui tampilan dulu
    catatan.hari = ada ? catatan.hari.filter(function (x) { return x !== w; }) : catatan.hari.concat([w]);
    gambarCatatan();
    api('PUT', '/api/catatan', { tanggal: catatan.tanggal, waktu: w, selesai: !ada }).then(function (j) {
      catatan = j; gambarCatatan();
    }).catch(function () { muatCatatan(); });
  };

  /* ---------- Pilih kota ---------- */
  var kin = $('kota'), dd = $('dd');
  kin.placeholder = 'Kota: ' + lokasi.nama;

  function daftar() {
    var q = kin.value.toLowerCase();
    var h = CFG.kota.filter(function (k) { return k.nama.toLowerCase().indexOf(q) !== -1; });
    dd.hidden = !h.length;
    dd.innerHTML = h.map(function (k) {
      return '<div data-k="' + esc(k.nama) + '" tabindex="0"><span>' + esc(k.nama) + '</span><span style="color:var(--sub)">Indonesia</span></div>';
    }).join('');
  }
  kin.onfocus = daftar;
  kin.oninput = daftar;

  function pilihKota(nama) {
    var k = CFG.kota.filter(function (x) { return x.nama === nama; })[0];
    if (!k) return;
    pindah(k);
    kin.value = '';
    kin.placeholder = 'Kota: ' + k.nama;
    dd.hidden = true;
    $('gpst').textContent = '';
  }
  dd.onclick = function (e) {
    var d = e.target.closest('[data-k]');
    if (d) pilihKota(d.dataset.k);
  };
  dd.onkeydown = function (e) {
    if (e.key !== 'Enter') return;
    var d = e.target.closest('[data-k]');
    if (d) pilihKota(d.dataset.k);
  };
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.cari')) dd.hidden = true;
  });

  function pindah(l) {
    lokasi = l;
    store.set('lokasi', l);
    simpanLokasiServer(l);
    perbaruiLokasiPush();
    data = null;
    $('info').textContent = l.nama + ' · memuat…';
    muatJadwal();
  }

  /* ---------- GPS ---------- */
  $('gps').onclick = function () {
    var st = $('gpst');
    if (!navigator.geolocation) { st.textContent = 'Peramban tidak mendukung lokasi.'; return; }
    st.textContent = 'Mencari lokasi…';
    navigator.geolocation.getCurrentPosition(function (pos) {
      var lat = pos.coords.latitude, lon = pos.coords.longitude;
      pindah({ nama: 'Lokasi saya', lat: +lat.toFixed(4), lon: +lon.toFixed(4), tz: zonaPerangkat });
      kin.placeholder = 'Lokasi saya (' + lat.toFixed(2) + ', ' + lon.toFixed(2) + ')';
      st.textContent = 'Lokasi dipakai.';
    }, function (err) {
      st.textContent = err.code === 1 ? 'Izin lokasi ditolak.' : 'Lokasi tidak ditemukan.';
    }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 600000 });
  };

  /* ---------- Pengingat + azan ---------- */
  var aktif = !!store.get('aktif', false);
  var azan = $('azan');
  var sudah = {}; // waktu yang sudah dibunyikan hari ini
  var ctxAudio = null;

  function chime() {
    try {
      ctxAudio = ctxAudio || new (window.AudioContext || window.webkitAudioContext)();
      [0, 0.45, 0.9].forEach(function (t) {
        var o = ctxAudio.createOscillator(), g = ctxAudio.createGain();
        o.type = 'sine'; o.frequency.value = 660;
        o.connect(g); g.connect(ctxAudio.destination);
        var s = ctxAudio.currentTime + t;
        g.gain.setValueAtTime(0.0001, s);
        g.gain.exponentialRampToValueAtTime(0.4, s + 0.05);
        g.gain.exponentialRampToValueAtTime(0.0001, s + 0.4);
        o.start(s); o.stop(s + 0.45);
      });
    } catch (e) {}
  }

  function mainkanAzan() {
    var pr = azan.play();
    if (pr && pr.catch) pr.catch(chime); // file azan.mp3 belum ada / diblokir → bunyi lonceng
  }
  function hentikanAzan() { try { azan.pause(); azan.currentTime = 0; } catch (e) {} }

  function bukaKunciAudio() {
    // Peramban hanya mengizinkan suara setelah ada klik pengguna
    try {
      azan.muted = true;
      var pr = azan.play();
      var beres = function () { azan.pause(); azan.currentTime = 0; azan.muted = false; };
      if (pr && pr.then) pr.then(beres).catch(function () { azan.muted = false; });
      else azan.muted = false;
    } catch (e) { azan.muted = false; }
    try {
      ctxAudio = ctxAudio || new (window.AudioContext || window.webkitAudioContext)();
      if (ctxAudio.state === 'suspended') ctxAudio.resume();
    } catch (e) {}
  }

  /* ---------- Push notification (tetap berbunyi walau halaman ditutup) ---------- */
  var pushAktif = false;   // peramban ini sudah berlangganan push
  var swSiap = null;

  function pushDidukung() {
    return !!(CFG.vapid && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window);
  }
  function base64KeBiner(b64) {
    var pad = '='.repeat((4 - b64.length % 4) % 4);
    var raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
    var out = new Uint8Array(raw.length);
    for (var i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
    return out;
  }
  function daftarkanSW() {
    if (!pushDidukung()) return Promise.reject(new Error('tidak didukung'));
    swSiap = swSiap || navigator.serviceWorker.register('/sw.js').then(function () { return navigator.serviceWorker.ready; });
    return swSiap;
  }
  function kirimLangganan(sub) {
    var j = sub.toJSON();
    return api('POST', '/api/push', {
      endpoint: j.endpoint, keys: j.keys,
      lat: lokasi.lat, lon: lokasi.lon, tz: lokasi.tz, nama: lokasi.nama
    });
  }
  function berlanggananPush() {
    return daftarkanSW().then(function (reg) {
      return reg.pushManager.getSubscription().then(function (sub) {
        return sub || reg.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: base64KeBiner(CFG.vapid)
        });
      });
    }).then(function (sub) {
      return kirimLangganan(sub).then(function () { pushAktif = true; });
    });
  }
  function berhentiPush() {
    if (!pushDidukung() || !('serviceWorker' in navigator)) return Promise.resolve();
    return navigator.serviceWorker.getRegistration('/sw.js').then(function (reg) {
      if (!reg) return;
      return reg.pushManager.getSubscription().then(function (sub) {
        if (!sub) return;
        var ep = sub.endpoint;
        return sub.unsubscribe().then(function () {
          return api('POST', '/api/push/berhenti', { endpoint: ep }).catch(function () {});
        });
      });
    }).then(function () { pushAktif = false; }).catch(function () { pushAktif = false; });
  }
  // Lokasi berubah: server perlu tahu supaya jadwal push mengikuti kota baru
  function perbaruiLokasiPush() {
    if (!pushAktif || !pushDidukung()) return;
    navigator.serviceWorker.getRegistration('/sw.js').then(function (reg) {
      return reg && reg.pushManager.getSubscription();
    }).then(function (sub) { if (sub) kirimLangganan(sub); }).catch(function () {});
  }
  function periksaPushAwal() {
    if (!pushDidukung() || !aktif || Notification.permission !== 'granted') return;
    berlanggananPush().then(function () { pasangTampilanPengingat(); }).catch(function () {});
  }

  function pasangTampilanPengingat(pesan) {
    $('btn').classList.toggle('on', aktif);
    $('ibtn').className = 'ti ' + (aktif ? 'ti-circle-check' : 'ti-bell');
    $('tbtn').textContent = aktif ? 'Pengingat aktif' : 'Aktifkan pengingat + azan';
    $('lonceng').classList.toggle('on', aktif);
    $('ilon').className = 'ti ' + (aktif ? 'ti-bell-ringing' : 'ti-bell-off');
    $('st').textContent = pesan || (aktif
      ? (pushAktif
          ? 'Aktif. Notifikasi tetap muncul walau halaman ditutup; azan berbunyi selama halaman terbuka.'
          : 'Azan dan notifikasi aktif selama halaman ini terbuka. Klik lagi untuk mematikan.')
      : 'Belum aktif.');
  }

  function nyalakan() {
    aktif = true;
    store.set('aktif', true);
    bukaKunciAudio();
    if (!('Notification' in window)) {
      pasangTampilanPengingat('Azan aktif. Peramban ini tidak mendukung notifikasi.');
    } else if (Notification.permission === 'granted') {
      pasangTampilanPengingat();
      mulaiPush();
    } else if (Notification.permission === 'denied') {
      pasangTampilanPengingat('Azan aktif. Notifikasi diblokir di pengaturan peramban.');
    } else {
      pasangTampilanPengingat();
      Notification.requestPermission().then(function (hasil) {
        if (hasil !== 'granted') pasangTampilanPengingat('Azan aktif. Notifikasi tidak diizinkan.');
        else mulaiPush();
      });
    }
  }
  function mulaiPush() {
    if (!pushDidukung()) return;
    berlanggananPush().then(function () { pasangTampilanPengingat(); }).catch(function () {
      pasangTampilanPengingat('Azan dan notifikasi aktif selama halaman terbuka. Notifikasi latar belakang belum bisa diaktifkan (butuh localhost atau HTTPS).');
    });
  }
  function matikan() {
    aktif = false;
    store.set('aktif', false);
    hentikanAzan();
    pasangTampilanPengingat();
    berhentiPush().then(function () { pasangTampilanPengingat(); });
  }
  function ganti() { aktif ? matikan() : nyalakan(); }
  $('btn').onclick = ganti;
  $('lonceng').onclick = ganti;

  function bunyikan(nama) {
    mainkanAzan();
    // Kalau push aktif, notifikasi sudah dikirim server: jangan dobel
    if (!pushAktif && 'Notification' in window && Notification.permission === 'granted') {
      try {
        var nt = new Notification('Waktu ' + nama + ' ' + lokasi.nama, {
          body: 'Saatnya salat ' + nama + '.',
          tag: 'salat-' + nama
        });
        nt.onclick = function () { hentikanAzan(); window.focus(); nt.close(); };
      } catch (e) {}
    }
  }

  function cekPengingat(L, n) {
    if (!aktif || !data) return;
    var hari = data.tanggal;
    L.forEach(function (x) {
      if (x[3] === 'Terbit') return; // terbit bukan waktu salat
      var telat = n - x[1];
      var kunci = hari + ':' + x[3];
      if (telat >= 0 && telat <= 120 && !sudah[kunci]) {
        sudah[kunci] = true;
        bunyikan(x[0]);
      }
    });
  }

  /* ---------- Doa, salin, favorit ---------- */
  var D = CFG.doa;
  var KAT = ['Semua'];
  D.forEach(function (d) { if (KAT.indexOf(d.kategori) === -1) KAT.push(d.kategori); });

  var kat = 'Semua', tab = 1;

  // Favorit disimpan menurut id doa. Format lama (daftar judul) diubah ke id.
  function idDariJudul(judul) {
    var d = D.filter(function (x) { return x.judul === judul; })[0];
    return d ? d.id : null;
  }
  function favLokal() {
    var out = [];
    store.get('fav', []).forEach(function (v) {
      var id = typeof v === 'number' ? v : idDariJudul(v);
      if (id !== null && out.indexOf(id) === -1) out.push(id);
    });
    return out;
  }
  var fav = new Set(USER ? USER.favorit : favLokal());

  if (USER) {
    // Gabungkan favorit lama di peramban ini ke akun, sekali saja
    var lama = favLokal();
    if (lama.length) {
      api('POST', '/api/favorit/sinkron', { ids: lama }).then(function (j) {
        fav = new Set(j.ids);
        store.set('fav', []);
        doa();
      }).catch(function () {});
    }
  }

  function ubahFav(id) {
    var ada = fav.has(id);
    if (!USER) {
      ada ? fav.delete(id) : fav.add(id);
      store.set('fav', Array.from(fav));
      doa();
      return;
    }
    // Perbarui tampilan dulu, lalu samakan dengan server
    ada ? fav.delete(id) : fav.add(id);
    doa();
    api(ada ? 'DELETE' : 'PUT', '/api/favorit/' + id).then(function (j) {
      fav = new Set(j.ids);
      doa();
    }).catch(function () {
      ada ? fav.add(id) : fav.delete(id);
      doa();
    });
  }

  function chips() {
    $('chips').innerHTML = KAT.map(function (c) {
      return '<span class="ch' + (c === kat ? ' a' : '') + '" data-c="' + esc(c) + '" tabindex="0">' + esc(c) + '</span>';
    }).join('');
  }

  function doa() {
    var q = $('cd').value.toLowerCase();
    var h = D.map(function (d, i) { return [d, i]; }).filter(function (x) {
      var d = x[0];
      return (tab === 3 ? fav.has(d.id) : true) &&
        (kat === 'Semua' || d.kategori === kat) &&
        (d.judul + ' ' + d.latin + ' ' + d.arti).toLowerCase().indexOf(q) !== -1;
    });

    $('lst').innerHTML = h.length ? h.map(function (x) {
      var d = x[0], i = x[1], f = fav.has(d.id);
      return '<div class="k">' +
        '<div style="display:flex;justify-content:space-between;align-items:center"><span style="font-size:16px;font-weight:500">' + esc(d.judul) + '</span><span class="ch" style="margin:0;cursor:default">' + esc(d.kategori) + '</span></div>' +
        '<p class="ar">' + esc(d.arab) + '</p>' +
        '<p style="font-style:italic;margin:0">' + esc(d.latin) + '</p>' +
        '<p style="font-size:13px;color:var(--sub);margin:6px 0 0">' + esc(d.arti) + '</p>' +
        '<div class="ab">' +
        '<button type="button" data-s="' + i + '"><i class="ti ti-copy" aria-hidden="true"></i><span>Salin</span></button>' +
        '<button type="button" data-f="' + i + '" class="' + (f ? 'f' : '') + '"><i class="ti ti-heart" aria-hidden="true"></i>' + (f ? 'Tersimpan' : 'Simpan') + '</button>' +
        '</div></div>';
    }).join('') : '<div class="kosong">' + (tab === 3 ? 'Belum ada doa favorit. Tekan Simpan di halaman Doa.' : 'Doa tidak ditemukan.') + '</div>';
  }

  function salinTeks(t) {
    if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
      return navigator.clipboard.writeText(t);
    }
    return new Promise(function (ok, gagal) {
      var ta = document.createElement('textarea');
      ta.value = t; ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy') ? ok() : gagal(); } catch (e) { gagal(e); }
      document.body.removeChild(ta);
    });
  }

  $('chips').onclick = function (e) {
    var c = e.target.closest('[data-c]');
    if (!c) return;
    kat = c.dataset.c; chips(); doa();
  };
  $('chips').onkeydown = function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var c = e.target.closest('[data-c]');
    if (!c) return;
    e.preventDefault(); kat = c.dataset.c; chips(); doa();
  };
  $('cd').oninput = doa;

  $('lst').onclick = function (e) {
    var f = e.target.closest('[data-f]'), s = e.target.closest('[data-s]');
    if (f) { ubahFav(D[+f.dataset.f].id); }
    if (s) {
      var d = D[+s.dataset.s], sp = s.querySelector('span');
      var t = d.judul + '\n' + d.arab + '\n' + d.latin + '\n' + d.arti;
      salinTeks(t).then(function () { sp.textContent = 'Tersalin'; })
        .catch(function () { sp.textContent = 'Gagal menyalin'; });
      setTimeout(function () { sp.textContent = 'Salin'; }, 1500);
    }
  };

  /* ---------- Halaman & menu bawah ----------
     Alamat berupa hash (#quran, #tasbih, …) supaya tombol Kembali peramban berfungsi.
     Halaman baru ditambah di HAL + fitur.js (isi lewat PSX.hook[nama]). */
  var HAL = {
    beranda: 'pg-beranda', quran: 'pg-quran', jadwal: 'p1', doa: 'p2', favorit: 'p2',
    tracker: 'pg-tracker', tasbih: 'pg-tasbih', asma: 'pg-asma', kiblat: 'pg-kiblat',
    masjid: 'pg-masjid', dzikir: 'pg-dzikir'
  };
  var hook = {};
  var halamanAktif = null;

  function tampil(nama) {
    if (!HAL[nama]) nama = 'beranda';
    halamanAktif = nama;
    tab = nama === 'favorit' ? 3 : 2;
    var el = $(HAL[nama]);
    Array.prototype.forEach.call(document.querySelectorAll('.pg'), function (x) { x.hidden = x !== el; });
    Array.prototype.forEach.call(document.querySelectorAll('.tabs a'), function (a) {
      a.classList.toggle('a', a.dataset.n === nama);
    });
    if (nama === 'doa' || nama === 'favorit') {
      $('jd').textContent = nama === 'favorit' ? 'Doa favorit saya' : 'Kumpulan doa harian';
      doa();
    }
    if (hook[nama]) hook[nama]();
    window.scrollTo(0, 0);
  }
  window.addEventListener('hashchange', function () { tampil(location.hash.replace('#', '')); });

  /* ---------- Mulai ---------- */
  chips();
  tampil(location.hash.replace('#', ''));
  pasangTampilanPengingat();
  periksaPushAwal();
  muatJadwal();
  setInterval(render, 1000);
  // Saat tab kembali terlihat, segarkan langsung
  document.addEventListener('visibilitychange', function () { if (!document.hidden) render(); });

  /* ---------- Dipakai fitur.js (halaman tambahan) ---------- */
  window.PSX = {
    $: $, p: p, esc: esc, store: store, hook: hook,
    lokasi: function () { return lokasi; },
    hariIni: function () { return kunciTanggal(lokasi.tz); },
    ulangiHalaman: function () { tampil(halamanAktif); }
  };
})();
