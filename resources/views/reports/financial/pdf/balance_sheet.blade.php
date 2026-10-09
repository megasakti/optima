<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LAPORAN POSISI KEUANGAN (NERACA) - {{ $companyName }}</title>
    <style>
        @page { margin: 28px 35px 28px 35px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .report-title { font-size: 15px; font-weight: bold; color: #111; text-align: right; text-transform: uppercase; }
        .period-sub { font-size: 9.5px; color: #666; text-align: right; }
        .columns-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .col-half { width: 49%; vertical-align: top; }
        .col-sep { width: 2%; }
        .section-box { border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; margin-bottom: 12px; }
        .section-title { font-weight: bold; color: #1e3a8a; font-size: 10.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 6px; text-transform: uppercase; }
        .sub-table { width: 100%; border-collapse: collapse; }
        .sub-table td { padding: 3px 2px; font-size: 9.5px; border-bottom: 1px solid #f8fafc; }
        .text-right { text-align: right; }
        .total-box { padding: 6px 10px; font-weight: bold; border-radius: 4px; margin-top: 6px; }
        .total-asset-box { background-color: #eff6ff; border: 1.5px solid #2563eb; color: #1e40af; }
        .total-pasiva-box { background-color: #f0fdf4; border: 1.5px solid #16a34a; color: #15803d; }
        .signatures-table { width: 100%; margin-top: 25px; }
        .sign-box { width: 45%; text-align: center; vertical-align: top; }
        .sign-space { height: 45px; }
        .footer-note { margin-top: 20px; border-top: 1px solid #eee; padding-top: 5px; text-align: center; font-size: 8.5px; color: #888; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="company-name">{{ $companyName }}</div>
                <div style="font-size: 9px; color: #555;">Lembaga Pelatihan Kerja & Tempat Uji Kompetensi BNSP Terlisensi<br>NPWP: 01.234.567.8-001.000</div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="report-title">LAPORAN POSISI KEUANGAN</div>
                <div class="period-sub">Per Tanggal {{ date('d F Y', strtotime($data['as_of_date'])) }}</div>
                <div style="font-size: 8.5px; color: #0284c7; text-align: right; font-weight: bold;">STANDAR SAK EMKM</div>
            </td>
        </tr>
    </table>

    <table class="columns-table">
        <tr>
            <!-- SISI KIRI: ASET -->
            <td class="col-half">
                <div class="section-box">
                    <div class="section-title">ASET LANCAR</div>
                    <table class="sub-table">
                        @foreach ($data['current_assets'] as $ca)
                            <tr>
                                <td style="width: 20%; color: #666;">{{ $ca['code'] }}</td>
                                <td style="width: 50%;">{{ $ca['name'] }}</td>
                                <td style="width: 30%;" class="text-right">Rp {{ number_format($ca['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; border-top: 1px solid #cbd5e1;">
                            <td colspan="2">Total Aset Lancar</td>
                            <td class="text-right">Rp {{ number_format($data['total_current_assets'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="section-box">
                    <div class="section-title">ASET TIDAK LANCAR (ASET TETAP)</div>
                    <table class="sub-table">
                        @foreach ($data['non_current_assets'] as $nca)
                            <tr>
                                <td style="width: 20%; color: #666;">{{ $nca['code'] }}</td>
                                <td style="width: 50%;">{{ $nca['name'] }}</td>
                                <td style="width: 30%;" class="text-right">Rp {{ number_format($nca['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; border-top: 1px solid #cbd5e1;">
                            <td colspan="2">Total Aset Tidak Lancar</td>
                            <td class="text-right">Rp {{ number_format($data['total_non_current_assets'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="total-box total-asset-box">
                    <table style="width: 100%;">
                        <tr>
                            <td>TOTAL ASET</td>
                            <td class="text-right" style="font-size: 11px;">Rp {{ number_format($data['total_assets'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </td>

            <td class="col-sep"></td>

            <!-- SISI KANAN: LIABILITAS & EKUITAS -->
            <td class="col-half">
                <div class="section-box">
                    <div class="section-title">LIABILITAS (UTANG JANGKA PENDEK)</div>
                    <table class="sub-table">
                        @foreach ($data['current_liabilities'] as $cl)
                            <tr>
                                <td style="width: 20%; color: #666;">{{ $cl['code'] }}</td>
                                <td style="width: 50%;">{{ $cl['name'] }}</td>
                                <td style="width: 30%;" class="text-right">Rp {{ number_format($cl['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; border-top: 1px solid #cbd5e1;">
                            <td colspan="2">Total Liabilitas</td>
                            <td class="text-right" style="color: #dc2626;">Rp {{ number_format($data['total_liabilities'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="section-box">
                    <div class="section-title">EKUITAS PEMILIK</div>
                    <table class="sub-table">
                        @foreach ($data['equities'] as $eq)
                            <tr>
                                <td style="width: 20%; color: #666;">{{ $eq['code'] }}</td>
                                <td style="width: 50%;">{{ $eq['name'] }}</td>
                                <td style="width: 30%;" class="text-right">Rp {{ number_format($eq['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" style="font-style: italic;">Laba Periode Berjalan</td>
                            <td class="text-right" style="color: #16a34a;">Rp {{ number_format($data['current_period_earnings'], 0, ',', '.') }}</td>
                        </tr>
                        <tr style="font-weight: bold; border-top: 1px solid #cbd5e1;">
                            <td colspan="2">Total Ekuitas</td>
                            <td class="text-right">Rp {{ number_format($data['total_equity'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="total-box total-pasiva-box">
                    <table style="width: 100%;">
                        <tr>
                            <td>TOTAL LIABILITAS & EKUITAS</td>
                            <td class="text-right" style="font-size: 11px;">Rp {{ number_format($data['total_liabilities_and_equity'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="signatures-table">
        <tr>
            <td class="sign-box">
                <div>Disiapkan Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Bagian Akuntansi</div>
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
