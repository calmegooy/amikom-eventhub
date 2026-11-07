<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Akun Penyelenggara - AmikomEventHub</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 40px 20px; color: #1e293b; }
        .container { max-width: 550px; margin: 0 auto; width: 100%; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .header { background-color: #e11d48; padding: 40px 30px; text-align: center; color: #ffffff; }
        .header h1 { font-size: 24px; font-weight: 900; margin: 0; }
        .header p { color: #ffe4e6; font-size: 14px; margin: 10px 0 0 0; }
        .body { padding: 40px 30px; line-height: 1.6; }
        .body h2 { font-size: 18px; font-weight: 700; margin-top: 0; }
        .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 30px; }
        .info-row { display: table; width: 100%; margin-bottom: 10px; }
        .info-label { display: table-cell; font-weight: bold; color: #64748b; width: 35%; font-size: 13px; text-transform: uppercase; }
        .info-value { display: table-cell; font-weight: bold; color: #0f172a; font-size: 14px; }
        .btn { display: inline-block; background-color: #64748b; color: #ffffff; padding: 14px 28px; border-radius: 12px; font-weight: bold; text-decoration: none; text-align: center; }
        .footer { text-align: center; padding: 30px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pemberitahuan Akun Penyelenggara</h1>
            <p>Akun Penyelenggara Ditangguhkan / Ditolak</p>
        </div>
        <div class="body">
            <h2>Halo {{ $organizer->name }},</h2>
            <p>
                Kami ingin menginformasikan bahwa pengajuan pendaftaran atau status keaktifan akun penyelenggara/kepanitiaan Anda untuk <strong>{{ $organizer->organization_name }}</strong> di **AmikomEventHub** saat ini ditolak atau ditangguhkan oleh administrator kami.
            </p>
            <p>
                Hal ini biasanya disebabkan oleh informasi profil yang kurang lengkap, nama organisasi yang tidak valid, atau adanya pelanggaran kebijakan platform.
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
                    <div class="info-value" style="color: #e11d48;">DITANGGUHKAN / DITOLAK</div>
                </div>
            </div>

            <p>
                Jika Anda merasa ini adalah kesalahan atau ingin mengajukan peninjauan kembali, silakan hubungi tim dukungan kami melalui menu bantuan atau hubungi administrator utama.
            </p>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('home') }}" class="btn">Kembali ke Beranda</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem AmikomEventHub.</p>
            <p>&copy; {{ date('Y') }} AmikomEventHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
