<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket Resmi - AmikomEventHub</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 20px; color: #334155; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #c7d2fe; font-size: 14px; }
        .content { padding: 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #1e293b; }
        .status-badge { display: inline-block; background-color: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 20px; letter-spacing: 1px; }
        .ticket-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 25px; }
        .ticket-card table { width: 100%; border-collapse: collapse; }
        .ticket-card td { padding: 8px 0; border-bottom: 1px dashed #cbd5e1; font-size: 14px; }
        .ticket-card td:last-child { text-align: right; font-weight: bold; color: #0f172a; }
        .ticket-card tr:last-child td { border-bottom: none; padding-bottom: 0; }
        .label { color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: bold; }
        .cta-container { text-align: center; margin: 35px 0 20px 0; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: bold; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; }
        .eco-note { margin-top: 10px; font-style: italic; color: #10b981; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>E-Ticket Anda Siap</h1>
            <p>Terima kasih telah menggunakan AmikomEventHub</p>
        </div>
        
        <div class="content">
            <div class="greeting">Halo, <strong>{{ $transaction->customer_name }}</strong>!</div>
            <span class="status-badge">PEMBAYARAN LUNAS</span>
            
            <p style="margin-top: 0;">Pesanan tiket Anda telah berhasil diproses. Berikut adalah rincian pesanan Anda yang juga berlaku sebagai bukti reservasi yang sah.</p>
            
            <div class="ticket-card">
                <table>
                    <tr>
                        <td><span class="label">Nama Event</span></td>
                        <td>{{ $transaction->event->title }}</td>
                    </tr>
                    <tr>
                        <td><span class="label">Order ID</span></td>
                        <td style="font-family: monospace; font-size: 16px;">{{ $transaction->order_id }}</td>
                    </tr>
                    <tr>
                        <td><span class="label">Jumlah Tiket</span></td>
                        <td>{{ $transaction->quantity }} Orang</td>
                    </tr>
                    <tr>
                        <td><span class="label">Total Pembayaran</span></td>
                        <td style="color: #10b981;">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 14px; text-align: center; color: #475569;">Klik tombol di bawah ini untuk melihat E-Ticket lengkap Anda beserta QR Code untuk Check-in di lokasi acara.</p>

            <div class="cta-container">
                <a href="{{ route('ticket', $transaction->order_id) }}" class="btn">Tampilkan QR Code Tiket</a>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} AmikomEventHub. Hak Cipta Dilindungi.</p>
            <p class="eco-note">🌱 AmikomEventHub mendukung inisiatif Green Tech. Tunjukkan tiket elektronik ini langsung dari perangkat Anda tanpa perlu dicetak.</p>
        </div>
    </div>
</body>
</html>