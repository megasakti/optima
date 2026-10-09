<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KWITANSI PEMBAYARAN - {{ $payment->payment_number }}</title>
    <style>
        @page {
            margin: 25px 35px 25px 35px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333333;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 17px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 3px;
        }
        .company-sub {
            font-size: 10px;
            color: #555555;
            line-height: 1.3;
        }
        .receipt-title {
            text-align: right;
            vertical-align: top;
        }
        .receipt-title h1 {
            font-size: 20px;
            color: #059669;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .receipt-number {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        .receipt-body {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .receipt-body td {
            padding: 8px 6px;
            vertical-align: top;
        }
        .receipt-label {
            width: 25%;
            font-weight: bold;
            color: #555555;
            font-size: 10.5px;
        }
        .receipt-colon {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }
        .receipt-val {
            width: 72%;
            font-size: 11px;
        }
        .highlight-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            color: #065f46;
        }
        .invoices-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .invoices-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
        }
        .invoices-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10.5px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 25px;
        }
        .sign-box {
            width: 35%;
            text-align: center;
            vertical-align: top;
        }
        .sign-space {
            height: 55px;
        }
        .paid-stamp {
            display: inline-block;
            border: 2px solid #059669;
            color: #059669;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            text-transform: uppercase;
            font-size: 10px;
            margin-top: 5px;
        }
        .footer-note {
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Kwitansi -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="company-name">{{ $payment->company->name ?? 'PT OPTIMA MITRA SERTIFIKASI' }}</div>
                <div class="company-sub">
                    Lembaga Pelatihan Kerja & Tempat Uji Kompetensi (TUK) BNSP Terlisensi<br>
                    Jl. Pemuda No. 128, Rawamangun, Jakarta Timur 13220<br>
                    Email: finance@optimatraining.co.id | NPWP: 01.234.567.8-001.000
                </div>
            </td>
            <td class="receipt-title">
                <h1>KWITANSI PEMBAYARAN</h1>
                <div class="receipt-number">{{ $payment->payment_number }}</div>
                <div class="paid-stamp">LUNAS / DITERIMA</div>
            </td>
        </tr>
    </table>

    <!-- Data Kwitansi -->
    <table class="receipt-body">
        <tr>
            <td class="receipt-label">Telah Diterima Dari</td>
            <td class="receipt-colon">:</td>
            <td class="receipt-val">
                <strong style="font-size: 13px; color: #111;">{{ $payment->customer->name }}</strong>
                @if ($payment->customer->address)
                    <div style="font-size: 10px; color: #666;">{{ $payment->customer->address }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="receipt-label">Jumlah Uang Diterima</td>
            <td class="receipt-colon">:</td>
            <td class="receipt-val">
                <div class="highlight-box">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
            </td>
        </tr>
        @if ((float) $payment->pph23_deducted > 0)
        <tr>
            <td class="receipt-label">Bukti Potong PPh 23</td>
            <td class="receipt-colon">:</td>
            <td class="receipt-val">
                Rp {{ number_format($payment->pph23_deducted, 0, ',', '.') }}
                <span style="font-size: 9px; color: #666;">(Total Kredit Piutang: Rp {{ number_format($payment->total_credited, 0, ',', '.') }})</span>
            </td>
        </tr>
        @endif
        <tr>
            <td class="receipt-label">Metode & Rekening Bank</td>
            <td class="receipt-colon">:</td>
            <td class="receipt-val">
                <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</strong> via {{ $payment->cashBankAccount->name }} ({{ $payment->cashBankAccount->account_number ?? '-' }})
                @if ($payment->reference_number)
                    <br><span style="font-size: 9.5px; color: #555;">No. Referensi: {{ $payment->reference_number }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="receipt-label">Untuk Pembayaran</td>
            <td class="receipt-colon">:</td>
            <td class="receipt-val">
                Pelunasan tagihan pelatihan dan sertifikasi kompetensi BNSP berikut:
                
                <table class="invoices-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">No. Faktur</th>
                            <th style="width: 25%;">Tgl Faktur</th>
                            <th style="width: 40%;" style="text-align: right;">Jumlah Dialokasikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->allocations as $alloc)
                            <tr>
                                <td><strong>{{ $alloc->invoice->invoice_number }}</strong></td>
                                <td>{{ $alloc->invoice->invoice_date->format('d/m/Y') }}</td>
                                <td style="text-align: right; font-weight: bold;">
                                    Rp {{ number_format($alloc->amount_applied, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        @if ($payment->notes)
        <tr>
            <td class="receipt-label">Catatan</td>
            <td class="receipt-colon">:</td>
            <td class="receipt-val" style="font-style: italic; color: #555;">
                {{ $payment->notes }}
            </td>
        </tr>
        @endif
    </table>

    <!-- Tanda Tangan Kwitansi -->
    <table class="signatures-table">
        <tr>
            <td style="width: 60%;"></td>
            <td class="sign-box">
                <div style="font-size: 10px;">Jakarta, {{ $payment->payment_date->format('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 2px;">Diterima Oleh Kasir / Finance:</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Siti Rahmawati, S.Ak.</div>
                <div style="font-size: 9px; color: #666;">Bagian Keuangan & Perbendaharaan</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Kwitansi ini adalah bukti penerimaan pembayaran yang sah dan telah dibukukan dalam Sistem Akuntansi OPTIMA FINANCE.
    </div>

</body>
</html>
