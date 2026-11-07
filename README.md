# Amikom Event Hub

Aplikasi reservasi tiket event online berbasis Laravel untuk mahasiswa dan penyelenggara profesional Universitas Amikom Yogyakarta. Sistem ini mendukung multi-tenant organizer, dynamic pricing, pembayaran terintegrasi, PWA, dan sertifikat otomatis.

---

## Live Demo & Tautan Penting

**Identitas Mahasiswa:**
- **Nama:** Yoga Adiyatma
- **NIM:** 24.12.3320
- **Jurusan:** Sistem Informasi (SI04)

**Tautan Akses Cepat:**
- **Beranda (Katalog):** [https://amikomeventhub-3320-main-se5rrk.free.laravel.cloud/](https://amikomeventhub-3320-main-se5rrk.free.laravel.cloud/)
- **Dashboard Admin/Organizer:** [https://amikomeventhub-3320-main-se5rrk.free.laravel.cloud/admin](https://amikomeventhub-3320-main-se5rrk.free.laravel.cloud/admin)
- **Repository:** [calmegooy/amikom-eventhub](https://github.com/calmegooy/amikom-eventhub)
- **Mirror InfinityFree:** [http://amikomeventhub-3320.free.je/?i=1](http://amikomeventhub-3320.free.je/?i=1)

---

## Panduan Setup & Demo Aplikasi

Berikut adalah panduan untuk menjalankan dan mendemonstrasikan fitur unggulan aplikasi ini secara lokal.

### 1. Setup Lokal
1. Pastikan Anda telah menginstal PHP 8.2+, Composer, Node.js, dan MySQL (misal via Laragon/XAMPP).
2. Clone repository dan masuk ke direktori proyek.
3. Jalankan `composer install` dan `npm install && npm run build`.
4. Salin `.env.example` ke `.env` lalu buat *application key* menggunakan `php artisan key:generate`.
5. Sesuaikan konfigurasi database Anda di `.env`.

### 2. Migration & Seeder (Aman)
Aplikasi ini tidak memiliki hardcode data dan sepenuhnya dinamis.
Jalankan perintah berikut untuk migrasi dan menyiapkan data dummy yang aman:
```bash
php artisan migrate --seed
```
*Catatan: Sangat dilarang untuk menjalankan `migrate:fresh` atau `db:wipe` pada environtment produksi. Skema dan relasi di database dirancang agar tetap terjaga integritasnya.*

### 3. Google SSO (Single Sign-On)
Aplikasi mendukung login cepat via Google.
- Pastikan `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` telah dikonfigurasi di file `.env` (atau Dashboard Hosting Anda).
- Pengguna dapat langsung mendaftar atau login ke akun yang sama tanpa perlu mengingat password lokal. 
- *Prosedur ini menggunakan OAuth2 yang diverifikasi langsung oleh Google Server, sangat aman dan token tidak disimpan sembarangan.*

### 4. Midtrans Sandbox & Pembayaran (BCA VA)
Checkout tiket berbayar terintegrasi dengan Midtrans Simulator.
- Kami memprioritaskan metode pembayaran **BCA Virtual Account**.
- Lakukan pemesanan pada event berbayar (pastikan Anda mengisi form checkout).
- Saat tiba di simulator Midtrans, pilih **BCA Virtual Account**.
- Buka web **Simulator BCA Midtrans** atau gunakan nomor **70335 + No HP** yang Anda input. Lakukan pembayaran.
- Webhook Midtrans idempoten akan memvalidasi pembayaran di background dan menerbitkan E-Ticket.

### 5. Scanner & QR Check-In
Event memiliki QR E-Ticket yang di-generate murni dari internal aplikasi demi keamanan.
- Organizer dapat login ke dalam dashboard lalu membuka menu **Scanner**.
- Panitia event dapat memindai QR Code E-Ticket dari HP/perangkat pembeli dengan kamera.
- Check-in hanya bisa sukses satu kali (mencegah *double entry*). Jika QR Code gagal di-scan, panitia dapat menggunakan *fallback* berupa form input kode alfanumerik.

### 6. Review H+1 (Ulasan)
Review adalah hal penting bagi reputasi sebuah event.
- Peserta yang **telah melakukan pembayaran lunas dan sukses check-in** dapat memberikan ulasan.
- Ulasan hanya bisa diberikan minimal **H+1** (Satu hari setelah event berakhir) untuk memastikan event benar-benar sudah terselenggara.
- Ulasan bersifat *tenant-isolated*, yang berarti rating organizer diukur berdasarkan nilai asli dan dinamis dari seluruh pesertanya.

### 7. Sertifikat Otomatis (E-Certificate)
Sistem memiliki pengiriman sertifikat *background jobs*.
- Peserta yang berhasil melakukan **Check-in**, otomatis berhak menerima sertifikat.
- Sertifikat akan dikirim ke email peserta dalam format PDF secara otomatis.
- Pastikan Anda menjalankan `php artisan queue:work` agar pemrosesan antrean sertifikat berjalan.

### 8. Progressive Web App (PWA)
Aplikasi ini sudah berstandar PWA:
- Terdapat `manifest.json` dan `sw.js` (Service Worker) yang valid.
- Aplikasi bisa di-Install ke Home Screen layaknya aplikasi native (Android/iOS/PC).
- Aset gambar, CSS, dan JS bersifat cacheable sehingga mempercepat *load-time*. Halaman sensitif yang memuat dinamika otorisasi user/session dengan sengaja **TIDAK** di-cache demi keamanan (Mencegah *Information Disclosure* secara offline).

### 9. WhatsApp Notification Fallback
EventHub mendukung notifikasi WhatsApp (misal: *Abandoned Cart Recovery* dan Pengiriman Tiket).
- Menggunakan skema API eksternal (contoh: Fonnte/Wablas) yang aman.
- **Lingkungan Development:** Bila API Token tidak diset pada `.env`, notifikasi tidak akan memblokir proses aplikasi, melainkan masuk ke *Logging Fallback*.
- Pesanan tiket Pending (Abandoned Cart) yang tidak kunjung dibayar dalam rentang waktu tertentu, akan diingatkan oleh bot otomatis melalui command `php artisan tickets:recover-carts`. 

### 10. Akun Demo & Multi-Tenant Role
Aplikasi ini memisahkan secara ketat *Tenant/Organizer* dan *Customer*. Data antar tenant 100% terisolasi (misalnya: Organizer A tidak bisa melihat penghasilan Organizer B).

Gunakan akun berikut untuk demo (tanpa password sensitif tertulis, default password adalah `password` kecuali jika Anda generate sendiri):

- **Superadmin:** `admin@example.com` (Bisa memanajemen semua tenant/kategori)
- **Organizer (Tenant 1):** `organizer@example.com` (Memiliki event dan akses scanner)
- **Customer:** `customer@example.com` (Untuk checkout event reguler)
*(Catatan: Anda dapat menggunakan `php artisan migrate --seed` pada tahap setup lokal untuk membuat data akun contoh ini)*

---
*Dikembangkan menggunakan Laravel 11. Didukung arsitektur Queue Job asinkronous, database transactional layer, serta pengujian PHPUnit yang komprehensif.*
