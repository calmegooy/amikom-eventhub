<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Organizer Disetujui - AmikomEventHub</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 40px 20px; color: #1e293b; }
        .container { max-width: 550px; margin: 0 auto; width: 100%; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .header { background-color: #059669; padding: 40px 30px; text-align: center; color: #ffffff; }
        .header h1 { font-size: 24px; font-weight: 900; margin: 0; }
        .header p { color: #d1fae5; font-size: 14px; margin: 10px 0 0 0; }
        .body { padding: 40px 30px; line-height: 1.6; }
        .body h2 { font-size: 18px; font-weight: 700; margin-top: 0; }
        .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 30px; }
        .info-row { display: table; width: 100%; margin-bottom: 10px; }
        .info-label { display: table-cell; font-weight: bold; color: #64748b; width: 35%; font-size: 13px; text-transform: uppercase; }
        .info-value { display: table-cell; font-weight: bold; color: #0f172a; font-size: 14px; }
        .btn { display: inline-block; background-color: #059669; color: #ffffff; padding: 14px 28px; border-radius: 12px; font-weight: bold; text-decoration: none; text-align: center; }
        .footer { text-align: center; padding: 30px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Selamat, {{ $organizer->organization_name }}!</h1>
            <p>Akun Penyelenggara Anda Telah Disetujui</p>
        </div>
        <div class="body">
            <h2>Halo {{ $organizer->name }},</h2>
            <p>
                Kami dengan senang hati memberi tahu Anda bahwa pengajuan akun kepanitiaan/organisasi Anda di <strong>AmikomEventHub</strong> telah disetujui oleh administrator kami.
            </p>
            <p>
                Sekarat Anda sudah memiliki akses penuh ke **Organizer Panel** untuk mempublikasikan event, membuat kupon promosi, memantau penjualan tiket secara langsung, dan mengunduh sertifikat event.
            </p>
            
            <div class="info-card">
                <div class="info-row">
                    <div class="info-label">Organisasi</div>
                    <div class="info-value">{{ $organizer->organization_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email Terdaftar</div>
                    <div class="info-value">{{ $organizer->email }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status Akun</div>
                    <div class="info-value" style="color: #059669;">AKTIF / DISETUJUI</div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('organizer.dashboard') }}" class="btn">Masuk Ke Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem AmikomEventHub.</p>
            <p>&copy; {{ date('Y') }} AmikomEventHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
