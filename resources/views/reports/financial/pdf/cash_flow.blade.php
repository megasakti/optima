<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LAPORAN ARUS KAS - {{ $companyName }}</title>
    <style>
        @page { margin: 28px 35px 28px 35px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10.5px; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .report-title { font-size: 15px; font-weight: bold; color: #111; text-align: right; text-transform: uppercase; }
        .period-sub { font-size: 9.5px; color: #666; text-align: right; }
        .statement-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .statement-table th { background-color: #f1f5f9; padding: 5px 8px; font-size: 10px; text-transform: uppercase; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
        .statement-table td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #f1f5f9; }
        .section-header { font-weight: bold; color: #1e3a8a; text-transform: uppercase; background-color: #f8fafc; }
        .total-row { font-weight: bold; border-top: 1px solid #94a3b8; border-bottom: 1px solid #94a3b8; }
        .grand-total-row { font-weight: bold; font-size: 11px; background-color: #eff6ff; border-top: 2px solid #2563eb; border-bottom: 2px solid #2563eb; color: #1d4ed8; }
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
                <div class="report-title">LAPORAN ARUS KAS</div>
                <div class="period-sub">Periode {{ date('d F Y', strtotime($data['start_date'])) }} s/d {{ date('d F Y', strtotime($data['end_date'])) }}</div>
                <div style="font-size: 8.5px; color: #0284c7; text-align: right; font-weight: bold;">STANDAR SAK EMKM</div>
            </td>
        </tr>
    </table>

    <table class="statement-table">
        <tbody>
            <!-- 1. ARUS KAS AKTIVITAS OPERASI -->
            <tr class="section-header">
                <td colspan="2">ARUS KAS DARI AKTIVITAS OPERASI</td>
                <td></td>
            </tr>
            <tr>
                <td style="width: 5%;"></td>
                <td style="width: 65%;">Penerimaan Kas dari Pelanggan & Penjualan Jasa Pelatihan</td>
                <td style="width: 30%;" class="text-right">Rp {{ number_format($data['operating_receipts'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Pembayaran Kas kepada Mitra LSP & Pemasok Bahan Pelatihan</td>
                <td class="text-right">(Rp {{ number_format($data['operating_payments'], 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td></td>
                <td>Pembayaran Kas untuk Beban Operasional, Gaji, & Sewa</td>
                <td class="text-right">(Rp {{ number_format($data['operating_other_payments'], 0, ',', '.') }})</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Arus Kas Bersih dari Aktivitas Operasi</td>
                <td class="text-right" style="color: {{ $data['net_operating_cash'] >= 0 ? '#16a34a' : '#dc2626' }};">
                    Rp {{ number_format($data['net_operating_cash'], 0, ',', '.') }}
                </td>
            </tr>

            <!-- 2. ARUS KAS AKTIVITAS INVESTASI -->
            <tr class="section-header">
                <td colspan="2">ARUS KAS DARI AKTIVITAS INVESTASI</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Perolehan / Penjualan Peralatan Kantor & Aset Tetap</td>
                <td class="text-right">Rp 0</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Arus Kas Bersih dari Aktivitas Investasi</td>
                <td class="text-right">Rp 0</td>
            </tr>

            <!-- 3. ARUS KAS AKTIVITAS PENDANAAN -->
            <tr class="section-header">
                <td colspan="2">ARUS KAS DARI AKTIVITAS PENDANAAN</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Penyetoran Modal Pemilik / (Penarikan Prive)</td>
                <td class="text-right">Rp 0</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Arus Kas Bersih dari Aktivitas Pendanaan</td>
                <td class="text-right">Rp 0</td>
            </tr>

            <!-- REKONSILIASI KAS -->
            <tr style="font-weight: bold; background-color: #f8fafc; border-top: 1.5px solid #94a3b8;">
                <td colspan="2">KENAIKAN / (PENURUNAN) BERSIH KAS & SETARA KAS</td>
                <td class="text-right" style="color: {{ $data['net_change_in_cash'] >= 0 ? '#16a34a' : '#dc2626' }};">
                    Rp {{ number_format($data['net_change_in_cash'], 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">SALDO KAS & SETARA KAS PADA AWAL PERIODE</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($data['beginning_cash'], 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total-row">
                <td colspan="2">SALDO KAS & SETARA KAS PADA AKHIR PERIODE</td>
                <td class="text-right">Rp {{ number_format($data['ending_cash'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures-table">
        <tr>
            <td class="sign-box">
                <div>Disiapkan Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Staf Akuntansi & Keuangan</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Siti Rahma, S.Ak.</div>
                <div style="font-size: 8.5px; color: #666;">Accounting Staff</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sign-box">
                <div>Disetujui Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Direktur Keuangan & Operasional</div>
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
