<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Certificate - {{ $transaction->customer_name }}</title>
    <style>
        @page { margin: 0; size: A4 landscape; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            color: #ffffff;
            text-align: center;
        }
        .cert-container {
            padding: 50px;
            border: 12px solid #f59e0b;
            margin: 20px;
            box-sizing: border-box;
            background: #0f172a;
        }
        .header {
            font-size: 14px;
            letter-spacing: 4px;
            color: #f59e0b;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .title {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .sub-header {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 30px;
        }
        .name {
            font-size: 34px;
            font-weight: bold;
            color: #38bdf8;
            border-bottom: 2px solid #f59e0b;
            display: inline-block;
            padding-bottom: 8px;
            margin-bottom: 25px;
        }
        .description {
            font-size: 14px;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        .event-title {
            color: #818cf8;
            font-weight: bold;
        }
        .footer-table {
            width: 100%;
            font-size: 11px;
            color: #64748b;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="header">AMIKOM Event Hub</div>
        <div class="title">SERTIFIKAT PENGHARGAAN</div>
        <div class="sub-header">Diberikan secara resmi kepada:</div>
        
        <div class="name">{{ strtoupper($transaction->customer_name) }}</div>
        
        <div class="description">
            Atas partisipasinya sebagai Peserta dalam acara <br>
            <span class="event-title">"{{ $event->title }}"</span>
        </div>

        <table class="footer-table">
            <tr>
                <td style="text-align: left;">
                    ID Sertifikat: <strong style="color: #ffffff;">CERT-{{ $transaction->order_id }}</strong>
                </td>
                <td style="text-align: right;">
                    Tanggal Terbit: <strong style="color: #ffffff;">{{ $date }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>