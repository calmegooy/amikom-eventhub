<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Certificate - {{ $transaction->order_id }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #0f172a;
            color: #ffffff;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .cert-container {
            width: 100%;
            height: 100vh;
            padding: 60px 40px;
            box-sizing: border-box;
            border: 15px solid #4f46e5;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }
        .header {
            font-size: 14px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #818cf8;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .title {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 2px;
            margin: 0 0 10px 0;
            color: #ffffff;
        }
        .subtitle {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 40px;
        }
        .recipient-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #cbd5e1;
            margin-bottom: 10px;
        }
        .recipient-name {
            font-size: 36px;
            font-weight: bold;
            color: #38bdf8;
            border-bottom: 2px solid #38bdf8;
            display: inline-block;
            padding-bottom: 8px;
            margin-bottom: 30px;
        }
        .event-title {
            font-size: 20px;
            color: #f8fafc;
            font-weight: bold;
            margin-bottom: 40px;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer-table {
            width: 100%;
            margin-top: 20px;
        }
        .footer-cell {
            width: 50%;
            vertical-align: bottom;
            font-size: 11px;
            color: #64748b;
        }
        .order-id {
            font-family: monospace;
            color: #818cf8;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="header">AMIKOM Event Hub</div>
        <div class="title">SERTIFIKAT PENGHARGAAN</div>
        <div class="subtitle">Diberikan secara resmi kepada:</div>

        <div class="recipient-name">{{ strtoupper($transaction->customer_name) }}</div>

        <div class="subtitle" style="margin-bottom: 10px;">Atas partisipasinya sebagai Peserta dalam acara:</div>
        <div class="event-title">"{{ $transaction->event->title }}"</div>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="footer-cell" style="text-align: left;">
                        ID Sertifikat: <span class="order-id">{{ $transaction->order_id }}</span><br>
                        Diterbitkan: {{ date('d F Y') }}
                    </td>
                    <td class="footer-cell" style="text-align: right;">
                        <strong style="color: #ffffff;">Panitia Penyelenggara</strong><br>
                        AMIKOM Event Hub System
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>