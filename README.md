# SIPAS-Informatika

**Sistem Informasi Pengajuan dan Pengelolaan Surat Akademik Program Studi Informatika Universitas Tanjungpura.**

## Link Repository GitHub

https://github.com/AnggunSagita14/SIPAS-Informatika

## Deskripsi
SIPAS Informatika merupakan sistem informasi berbasis web yang dikembangkan untuk membantu proses pengelolaan surat akademik mahasiswa Program Studi Informatika Universitas Tanjungpura, mulai dari pencatatan pengajuan, verifikasi, penerbitan, hingga pemantauan status surat.

## Fitur Sistem

### Admin
- Login admin.
- Dashboard pengelolaan surat.
- Pengelolaan data mahasiswa.
- Pengelolaan jenis surat.
- Pencatatan pengajuan surat mahasiswa.
- Verifikasi dan penolakan pengajuan.
- Penerbitan surat akademik.
- Pengelolaan riwayat pengajuan dan surat terbit.

### Mahasiswa
- Melakukan pengajuan surat secara langsung melalui pelayanan administrasi.
- Mengecek status pengajuan menggunakan kode surat dan kode akses.
- Memantau perkembangan pengajuan melalui fitur Jejak Surat.

## Teknologi yang Digunakan
- PHP sebagai bahasa pemrograman backend.
- MySQL sebagai sistem manajemen basis data.
- HTML5 untuk membangun struktur halaman web.
- CSS3 untuk mengatur tampilan antarmuka.
- JavaScript untuk menambahkan interaksi pada halaman web.
- Bootstrap 5 sebagai framework CSS untuk membantu pembuatan tampilan responsif.
- Laragon sebagai lingkungan pengembangan server lokal.

## Progres Pengerjaan

## Progres Pengerjaan

1. 13/11/2026 – Tahap 1
   Melakukan identifikasi kebutuhan Sistem Informasi Pengajuan dan Pengelolaan Surat Akademik Program Studi Informatika Universitas Tanjungpura, meliputi proses pengajuan, verifikasi, penerbitan, dan pemantauan surat akademik.

2. 16/11/2026 – Tahap 1
   Menentukan alur kerja sistem dengan Admin sebagai pengelola data mahasiswa, pengajuan, verifikasi, dan penerbitan surat. Mahasiswa dapat memantau perkembangan pengajuan melalui fitur Jejak Surat menggunakan kode surat dan kode akses tanpa perlu melakukan login.

3. 20/11/2026 – Tahap 1
   Melakukan perancangan dan implementasi awal database menggunakan MySQL, meliputi tabel pengguna, peran, jenis surat, pengajuan, riwayat verifikasi, dan surat terbit beserta relasi antartabel.

4. 20/11/2026 – Tahap 1
   Mengidentifikasi kebutuhan data dan fungsional sistem berdasarkan hak akses Admin dan Mahasiswa, meliputi pengelolaan data mahasiswa, jenis surat, pencatatan pengajuan, verifikasi, penerbitan surat, serta pemantauan status pengajuan melalui fitur Jejak Surat menggunakan kode surat dan kode akses.

## Instalasi
1. Clone repository ini.
2. Simpan folder proyek di direktori `www` Laragon.
3. Buat database MySQL dengan nama `sipas_informatika`.
4. Sesuaikan konfigurasi database pada `config/database.php`.
5. Jalankan Apache dan MySQL melalui Laragon.
6. Buka sistem melalui browser:
   `http://localhost/SIPAS-Informatika/`
