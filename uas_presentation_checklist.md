# Checklist Presentasi UAS AmikomEventHub (4 Menit)

Gunakan checklist ini untuk mendemonstrasikan fitur unggulan aplikasi dalam waktu 4 menit.
Pastikan aplikasi berjalan (`php artisan serve`, `npm run dev`) dan buka terminal ekstra untuk menjalankan `php artisan queue:work`.

## Persiapan (0:00 - 0:30)
- [ ] Buka Aplikasi (PWA) di Google Chrome.
- [ ] Tunjukkan indikator install PWA dan jelaskan singkat service worker yang sudah aktif.
- [ ] Tunjukkan terminal di background yang menjalankan `php artisan queue:work` untuk memproses job asinkron.

## 1. Checkout & Multi-tier Pricing (0:30 - 1:15)
- [ ] Login sebagai customer.
- [ ] Buka halaman detail Event yang memiliki harga bertingkat (Early Bird/Presale).
- [ ] Buka halaman Checkout.
- [ ] Tunjukkan UI badge harga ("Early Bird") beserta harga normal yang dicoret.
- [ ] Lakukan pemesanan tiket.

## 2. Fitur Abandoned Cart (1:15 - 1:45)
- [ ] Biarkan transaksi berada dalam status Pending.
- [ ] Buka terminal baru, jalankan command: `php artisan tickets:recover-carts`.
- [ ] Tunjukkan log (atau output terminal) bahwa sistem mendeteksi transaksi pending dan mengirim notifikasi WhatsApp abandoned cart.

## 3. Pembayaran & E-Ticket WhatsApp Background (1:45 - 2:30)
- [ ] Selesaikan pembayaran pesanan tadi menggunakan Midtrans Simulator BCA VA Sandbox.
- [ ] Perhatikan terminal `queue:work`.
- [ ] Tunjukkan bahwa setelah pembayaran sukses, job `SendTicketWhatsAppJob` otomatis diproses di background tanpa membuat user menunggu.
- [ ] Tunjukkan halaman detail Tiket Saya.

## 4. Check-in PWA & Auto-Sertifikat (2:30 - 3:30)
- [ ] Buka Chrome DevTools -> Application -> Service Workers, set centang "Offline" (atau matikan WiFi).
- [ ] Refresh halaman. Tunjukkan bahwa PWA tetap load berkat cache aset Service Worker.
- [ ] Matikan Offline mode.
- [ ] Buka browser incognito, login sebagai Organizer.
- [ ] Buka halaman Scanner dan scan tiket QR customer yang baru saja dibeli.
- [ ] Perlihatkan check-in sukses dan validasi pencegahan check-in ganda (scan 2x).
- [ ] Perhatikan terminal `queue:work` lagi.
- [ ] Tunjukkan bahwa `SendCertificateJob` berjalan otomatis setelah check-in sukses di database.

## 5. Penutup (3:30 - 4:00)
- [ ] Sampaikan ringkasan arsitektur (Idempotency, PWA, Async Jobs, Automated Tests).
- [ ] Tutup presentasi.
