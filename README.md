# Perpustakaan Digital

Website perpustakaan berbasis Laravel dengan katalog yang dapat dicari dan difilter, autentikasi anggota, pengajuan peminjaman, dashboard admin, dan transaksi pengembalian.

## Menjalankan aplikasi

```powershell
composer install
npm install
php artisan migrate --seed
npm run dev
```

Jalankan `php artisan serve` pada terminal lain, lalu buka `http://localhost:8000`. Untuk membuat aset produksi, jalankan `npm run build`.

Database bawaan menggunakan SQLite (`database/database.sqlite`). Atur koneksi pada `.env` bila ingin memakai database lain.

## Akun demo

| Peran | Email | Kata sandi |
|---|---|---|
| Anggota | `anggota@perpus.id` | `password` |
| Administrator | `admin@perpus.id` | `password` |

Ganti kata sandi demo sebelum aplikasi digunakan di lingkungan yang dapat diakses publik.

## Fitur

- Halaman beranda, katalog, detail buku, berita, tentang, dan kontak responsif.
- Pencarian katalog dengan filter kategori, penulis, tahun, ketersediaan, dan pengurutan; hasil menggunakan pagination.
- Registrasi, login/logout sesi, reset kata sandi melalui email, profil anggota, dashboard, dan riwayat.
- Alur transaksi: anggota mengajukan pinjam; admin menyetujui/menolak dan memproses pengembalian. Stok diperbarui dalam transaksi database, masa pinjam 14 hari, perpanjangan satu kali selama 7 hari, serta denda Rp1.000 per hari terlambat.
- Dashboard admin untuk statistik, manajemen buku dan anggota, serta pemrosesan transaksi.
- Ringkasan aktivitas peminjaman enam bulan dan ekspor laporan CSV buku, anggota, peminjaman, pengembalian, serta denda (format kompatibel dengan spreadsheet).
- Pesan kontak disimpan untuk ditindaklanjuti administrator.

## Pengembangan

Jalankan pengujian dengan `php artisan test`. Build aset frontend menggunakan `npm run build`.
