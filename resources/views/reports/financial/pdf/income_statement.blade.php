<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LAPORAN LABA RUGI - {{ $companyName }}</title>
    <style>
        @page { margin: 28px 35px 28px 35px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10.5px; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .report-title { font-size: 15px; font-weight: bold; color: #111; text-align: right; text-transform: uppercase; }
        .period-sub { font-size: 9.5px; color: #666; text-align: right; }
        .statement-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .statement-table th { background-color: #f1f5f9; padding: 5px 8px; font-size: 10px; text-transform: uppercase; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
        .statement-table td { padding: 4.5px 8px; font-size: 10px; border-bottom: 1px solid #f1f5f9; }
        .section-header { font-weight: bold; color: #1e3a8a; text-transform: uppercase; background-color: #f8fafc; }
        .total-row { font-weight: bold; border-top: 1px solid #94a3b8; border-bottom: 1px solid #94a3b8; }
        .grand-total-row { font-weight: bold; font-size: 11px; background-color: #f0fdf4; border-top: 2px solid #16a34a; border-bottom: 2px solid #16a34a; color: #15803d; }
        .text-right { text-align: right; }
        .signatures-table { width: 100%; margin-top: 25px; }
        .sign-box { width: 45%; text-align: center; vertical-align: top; }
        .sign-space { height: 50px; }
        .footer-note { margin-top: 25px; border-top: 1px solid #eee; padding-top: 5px; text-align: center; font-size: 8.5px; color: #888; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="company-name">{{ $companyName }}</div>
                <div style="font-size: 9px; color: #555;">Lembaga Pelatihan & TUK Sertifikasi BNSP Terlisensi<br>NPWP: 01.234.567.8-001.000</div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="report-title">LAPORAN LABA RUGI</div>
                <div class="period-sub">Periode {{ date('d F Y', strtotime($data['start_date'])) }} s/d {{ date('d F Y', strtotime($data['end_date'])) }}</div>
                <div style="font-size: 8.5px; color: #0284c7; text-align: right; font-weight: bold;">STANDAR SAK EMKM</div>
            </td>
        </tr>
    </table>

    <table class="statement-table">
        <tbody>
            <!-- PENDAPATAN USAHA -->
            <tr class="section-header">
                <td colspan="2">PENDAPATAN USAHA</td>
                <td></td>
            </tr>
            @foreach ($data['revenues'] as $rev)
                <tr>
                    <td style="width: 15%; color: #666;">{{ $rev['code'] }}</td>
                    <td style="width: 55%;">{{ $rev['name'] }}</td>
                    <td style="width: 30%;" class="text-right">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL PENDAPATAN USAHA</td>
                <td class="text-right" style="color: #1e3a8a;">Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}</td>
            </tr>

            <!-- BEBAN POKOK PELATIHAN -->
            <tr class="section-header">
                <td colspan="2">BEBAN POKOK PELATIHAN & SERTIFIKASI (HPP)</td>
                <td></td>
            </tr>
            @foreach ($data['cogs'] as $c)
                <tr>
                    <td style="color: #666;">{{ $c['code'] }}</td>
                    <td>{{ $c['name'] }}</td>
                    <td class="text-right">Rp {{ number_format($c['amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL BEBAN POKOK PELATIHAN</td>
                <td class="text-right" style="color: #dc2626;">(Rp {{ number_format($data['total_cogs'], 0, ',', '.') }})</td>
            </tr>

            <!-- LABA KOTOR -->
            <tr style="font-weight: bold; background-color: #eff6ff; border-top: 1.5px solid #2563eb; border-bottom: 1.5px solid #2563eb;">
                <td colspan="2">LABA KOTOR (GROSS PROFIT)</td>
                <td class="text-right" style="color: #1d4ed8;">Rp {{ number_format($data['gross_profit'], 0, ',', '.') }}</td>
            </tr>

            <!-- BEBAN OPERASIONAL -->
            <tr class="section-header">
                <td colspan="2">BEBAN OPERASIONAL & ADMINISTRASI UMUM</td>
                <td></td>
            </tr>
            @foreach ($data['operating_expenses'] as $opex)
                <tr>
                    <td style="color: #666;">{{ $opex['code'] }}</td>
                    <td>{{ $opex['name'] }}</td>
                    <td class="text-right">Rp {{ number_format($opex['amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL BEBAN OPERASIONAL</td>
                <td class="text-right">(Rp {{ number_format($data['total_operating_expenses'], 0, ',', '.') }})</td>
            </tr>

            <!-- LABA OPERASIONAL -->
            <tr style="font-weight: bold; background-color: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                <td colspan="2">LABA OPERASIONAL (OPERATING PROFIT)</td>
                <td class="text-right">Rp {{ number_format($data['operating_profit'], 0, ',', '.') }}</td>
            </tr>

            <!-- LABA BERSIH -->
            <tr class="grand-total-row">
                <td colspan="2">LABA BERSIH PERIODE BERJALAN (NET PROFIT)</td>
                <td class="text-right">Rp {{ number_format($data['net_profit'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures-table">
        <tr>
            <td class="sign-box">
                <div>Disiapkan Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Bagian Keuangan & Akuntansi</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Siti Rahma, S.Ak.</div>
                <div style="font-size: 8.5px; color: #666;">Accounting Staff</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sign-box">
                <div>Disetujui Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Finance & Operational Manager</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Dr. H. Budi Santoso, S.E., M.M.</div>
                <div style="font-size: 8.5px; color: #666;">Direktur Keuangan</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Laporan Keuangan Resmi dicetak secara sistematis melalui Aplikasi OPTIMA FINANCE pada {{ date('d F Y H:i') }}.
    </div>

</body>
</html>
