# Potensi Mojo — Phase 1

Instalasi lokal (XAMPP):

1. Salin folder `potensi-mojo` ke `C:/xampp/htdocs/`.
2. Jalankan Apache di XAMPP.
3. Akses: http://localhost/potensi-mojo/

Catatan:
- Phase 1: UI statis dengan data dummy. Database dan admin akan ditambahkan pada fase berikutnya.
- Gunakan PHP 8+ dan MySQL ketika melanjutkan ke integrasi database.

Setup database Phase 3:

1. Buka phpMyAdmin, pilih menu Import, lalu import `database.sql`.
2. Pastikan MySQL dan Apache aktif di XAMPP.
3. Login admin melalui http://localhost/potensi-mojo/admin/login.php
4. Akun development: username `admin` dengan password `password` (atau `admin123` pada database lokal).
5. Ganti password development tersebut sebelum deployment publik.

yang belum
- design (masi harus perbaiki referensi dari pinterest)
- daftar potensi (survei ke pak dukuh minta verifikasi data)
- belum nemu pengelola
- foto foto dokumentasi (hasil kerajinan, foto pengelola)
- bagian admin edit lokasi sepertinya gaperlu pake longtitude langsung link ke google maps aja
- bagian login sudah diubah menggunakan username (admin) dan password.
-