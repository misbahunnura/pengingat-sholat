# Pengingat Salat (Laravel)

Jadwal salat nyata (Aladhan API, metode Kemenag RI), hitung mundur, pencarian kota, GPS,
pengingat + azan, kumpulan doa, favorit, tema gelap/terang, dan menu bawah ala aplikasi.

## Link Website

🔗 http://pengingat-sholat.ifree.page/

## Anggota Kelompok

| No | Nama | NIM |
|----|------|-----|
| 1 | [Misbahun nura] | [24105111086] |
| 2 | [Hayatun nufus] | [24105111070] |
| 3 | [Nelly agustiani] | [24105111119] |

Fitur dengan database (SQLite, tanpa perlu MySQL):

| Fitur | Keterangan |
|---|---|
| Akun | Daftar, masuk, keluar. Favorit dan kota pilihan ikut berpindah antar HP dan laptop. |
| Admin doa | Tambah, ubah, hapus doa lewat web (`/admin/doa`), khusus akun admin. |
| Push notification | Pengingat waktu salat tetap muncul walau halaman ditutup. |
| Catatan salat | Tandai 5 salat harian, grafik 7 hari, dan rangkaian hari lengkap (khusus yang login). |
| Notifikasi saran | Admin melihat lencana jumlah saran baru (ikon pesan). Pengguna melihat lencana dan kartu di beranda saat saran dibalas atau statusnya berubah. |
| Dzikir pagi & petang | Halaman `#dzikir` dengan hitungan tap per bacaan, tersimpan per hari di perangkat. |
| Admin kelola konten | Menu admin: Saran, Doa, Dzikir, Asmaul Husna (`/admin/doa`, `/admin/dzikir`, `/admin/asma`). |
| Mode instal (PWA) | Bisa dipasang di layar utama, ikon dan layar penuh seperti aplikasi, beranda terakhir tetap terbuka saat offline. |

Tanpa login, aplikasi tetap bisa dipakai penuh seperti sebelumnya (favorit tersimpan di peramban).

## Cara menjalankan

Syarat: PHP 8.2+ (ekstensi `pdo_sqlite`, `openssl`, `mbstring`, `curl`) dan Composer.

```bash
composer require minishlink/web-push      # memasang semua paket + paket push
cp .env.example .env                       # lewati kalau .env sudah ada
php artisan key:generate                   # lewati kalau APP_KEY sudah terisi
```

Ubah di `.env` **sebelum** langkah berikutnya:

```
ADMIN_EMAIL=emailanda@contoh.com
ADMIN_PASSWORD=kata-sandi-yang-kuat
```

```bash
php artisan migrate --seed                 # buat tabel, isi doa dari doa.json, buat akun admin
php artisan salat:vapid --simpan           # buat kunci push, ditulis ke .env
php artisan config:clear
php artisan serve
```

Buka http://localhost:8000. Masuk dengan akun admin tadi, lalu ikon gerigi di pojok kanan atas membuka halaman kelola doa.

Seeder hanya mengisi doa, Asmaul Husna, dan dzikir kalau tabelnya masih kosong, jadi perubahan dari halaman admin tidak tertimpa.
Setelah itu `resources/data/doa.json` tidak dipakai lagi (isi doa ada di database).

## Mengaktifkan push notification

Push butuh dua hal selain langkah di atas:

1. **Penjadwal berjalan tiap menit.** Perintah `salat:kirim` mengecek waktu salat setiap menit.
   - Saat mencoba di komputer sendiri: biarkan satu terminal tambahan menjalankan `php artisan schedule:work`.
   - Di server: tambahkan cron `* * * * * php /path/ke/proyek/artisan schedule:run >> /dev/null 2>&1`.
2. **localhost atau HTTPS.** Peramban hanya mengizinkan push dari `http://localhost` atau situs HTTPS.
   Lewat alamat IP biasa (misalnya `http://192.168.1.5:8000`) push tidak bisa diaktifkan; azan di halaman tetap jalan.

Cara mencoba: buka aplikasi, klik **Aktifkan pengingat + azan**, izinkan notifikasi, lalu jalankan
`php artisan salat:kirim --uji` untuk mengirim notifikasi percobaan ke semua perangkat yang berlangganan.

Catatan: iPhone hanya menerima push kalau aplikasi ditambahkan ke Layar Utama (iOS 16.4+).

## Azan

Taruh file `azan.mp3` di `public/audio/`. Tanpa file itu, pengingat berbunyi nada lonceng.
Suara azan hanya berbunyi selama halaman terbuka; push hanya menampilkan notifikasi.

## Mengubah isi

| Yang diubah | Lokasi |
|---|---|
| Daftar kota (koordinat & zona waktu) | `config/salat.php` |
| Metode hitung | `SALAT_METODE` di `.env` (20 = Kemenag RI) |
| Daftar doa | Halaman admin `/admin/doa` |
| Tampilan | `public/css/app.css`, `resources/views/` |
| Logika halaman | `public/js/app.js` |
| Pengambilan jadwal + cache harian | `app/Services/JadwalSalat.php` |
| Pengiriman push | `app/Console/Commands/KirimPengingat.php`, `public/sw.js` |

## Menjadikan akun lain sebagai admin

Pendaftaran lewat formulir tidak pernah menghasilkan admin. Untuk mengangkat akun yang sudah ada:

```bash
php artisan db:seed --class=AdminSeeder     # membuat/menyetel ulang admin dari .env
```

## Produksi

Set `APP_ENV=production`, `APP_DEBUG=false`, ganti `ADMIN_PASSWORD`, dan arahkan root web server ke folder `public/`.
Pastikan `storage/`, `bootstrap/cache/`, dan `database/` (beserta `database.sqlite`) bisa ditulis.
Jalankan cron penjadwal, dan gunakan HTTPS supaya push notification bisa dipakai.
Jadwal diambil server dari `api.aladhan.com`, jadi server harus bisa keluar ke internet.

## Memperbarui dari versi sebelumnya

Ekstrak zip pembaruan ke folder proyek (timpa berkas yang sama; zip ini tidak berisi `.env` dan database), lalu:

```bash
php artisan migrate --seed     # tabel baru: dzikir, asma, kolom notifikasi saran
php artisan view:clear
```

## Mode instal (PWA)

- Butuh **HTTPS** atau `http://localhost`. Lewat alamat IP biasa tombol pasang tidak muncul.
- Android/Chrome: kartu **Pasang di layar utama** muncul di beranda. iPhone: Safari > Bagikan > Tambah ke Layar Utama.
- Service worker ada di `public/sw.js`. Kalau kamu mengubah daftar `PRECACHE` di dalamnya, naikkan `VERSI`.
- Saat keluar akun, salinan beranda di perangkat otomatis dihapus.
- Saat mengembangkan: CSS/JS selalu diambil dari jaringan lebih dulu, jadi perubahan langsung terlihat.
