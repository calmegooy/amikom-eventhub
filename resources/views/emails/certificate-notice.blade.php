<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Certificate Kehadiran</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #059669, #065f46); padding: 40px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .header p { color: #a7f3d0; margin: 8px 0 0; }
        .body { padding: 32px 40px; }
        .highlight { background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 12px; padding: 20px; margin: 24px 0; }
        .highlight p { margin: 0; color: #065f46; font-size: 15px; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; color: #64748b; font-size: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 E-Certificate Kehadiran</h1>
            <p>Selamat! Anda telah berhasil hadir dan check-in.</p>
        </div>
        <div class="body">
            <p>Halo, <strong>{{ $transaction->customer_name }}</strong>,</p>
            <p>Terima kasih telah hadir di event <strong>{{ $transaction->event->title }}</strong>. Kehadiran Anda telah kami catat.</p>

            <div class="highlight">
                <p>📎 Terlampir adalah <strong>E-Certificate Kehadiran</strong> Anda dalam format PDF.<br>
                Silakan unduh dan simpan sebagai bukti partisipasi Anda.</p>
                <div style="margin-top: 15px; text-align: center;">
                    <a href="{{ route('certificate.index', $transaction->order_id) }}" style="display: inline-block; background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 14px;">Buka Portal Sertifikat</a>
                </div>
            </div>

            <p>Sampai jumpa di event AmikomEventHub berikutnya! 🚀</p>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem AmikomEventHub.<br>
            Jangan balas email ini.</p>
        </div>
    </div>
</body>
</html>
