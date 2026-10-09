<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PAYMENT VOUCHER - {{ $payment->payment_number }}</title>
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
            border-bottom: 2px solid #b91c1c;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 17px;
            font-weight: bold;
            color: #b91c1c;
            margin-bottom: 3px;
        }
        .company-sub {
            font-size: 10px;
            color: #555555;
            line-height: 1.3;
        }
        .voucher-title {
            text-align: right;
            vertical-align: top;
        }
        .voucher-title h1 {
            font-size: 19px;
            color: #b91c1c;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .voucher-number {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        .voucher-body {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .voucher-body td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .voucher-label {
            width: 25%;
            font-weight: bold;
            color: #555555;
            font-size: 10.5px;
        }
        .voucher-colon {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }
        .voucher-val {
            width: 72%;
            font-size: 11px;
        }
        .amount-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            color: #991b1b;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
        }
        .items-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .sign-cell {
            width: 25%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #dddddd;
            padding: 6px;
        }
        .sign-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #555555;
            text-transform: uppercase;
            margin-bottom: 45px;
        }
        .sign-name {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px dotted #888888;
            padding-top: 3px;
        }
        .footer-note {
            margin-top: 25px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                <div class="company-name">{{ $payment->company->name ?? 'PT OPTIMA MITRA SERTIFIKASI' }}</div>
                <div class="company-sub">
                    Lembaga Pelatihan Kerja & Tempat Uji Kompetensi (TUK) BNSP Terlisensi<br>
                    Jl. Pemuda No. 128, Rawamangun, Jakarta Timur 13220<br>
                    Email: finance@optimatraining.co.id
                </div>
            </td>
            <td class="voucher-title">
                <h1>BUKTI KAS KELUAR</h1>
                <div class="voucher-number">{{ $payment->payment_number }}</div>
                <div style="font-size: 10px; color: #555; margin-top: 3px;">PAYMENT VOUCHER</div>
            </td>
        </tr>
    </table>

    @php
        $payee = $payment->lsp ?? $payment->vendor ?? $payment->trainer;
    @endphp

    <!-- Rincian Pengeluaran -->
    <table class="voucher-body">
        <tr>
            <td class="voucher-label">Dibayarkan Kepada</td>
            <td class="voucher-colon">:</td>
            <td class="voucher-val">
                <strong style="font-size: 12px; color: #111;">{{ $payee?->name ?? 'Vendor' }}</strong>
                @if ($payee?->address)
                    <div style="font-size: 10px; color: #666;">{{ $payee->address }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="voucher-label">Jumlah Uang Keluar</td>
            <td class="voucher-colon">:</td>
            <td class="voucher-val">
                <div class="amount-box">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="voucher-label">Sumber Kas / Bank</td>
            <td class="voucher-colon">:</td>
            <td class="voucher-val">
                <strong>{{ $payment->cashBankAccount->name }}</strong> ({{ $payment->cashBankAccount->account_number ?? 'Kas Fisik' }})
                &mdash; Metode: {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                @if ($payment->reference_number)
                    <br><span style="font-size: 9.5px; color: #555;">No. Referensi Bank: {{ $payment->reference_number }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="voucher-label">Tanggal Bayar</td>
            <td class="voucher-colon">:</td>
            <td class="voucher-val">{{ $payment->payment_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="voucher-label">Untuk Keperluan</td>
            <td class="voucher-colon">:</td>
            <td class="voucher-val">
                Pelunasan tagihan kewajiban biaya operasional pelatihan / sertifikasi BNSP:
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">No. Tagihan</th>
                            <th style="width: 20%;">Tgl Tagihan</th>
                            <th style="width: 30%;">Total Tagihan</th>
                            <th style="width: 25%;" style="text-align: right;">Dibayarkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->allocations as $alloc)
                            <tr>
                                <td><strong>{{ $alloc->vendorBill->bill_number }}</strong></td>
                                <td>{{ $alloc->vendorBill->bill_date->format('d/m/Y') }}</td>
                                <td>Rp {{ number_format($alloc->vendorBill->total_amount, 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: bold; color: #991b1b;">
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
            <td class="voucher-label">Catatan</td>
            <td class="voucher-colon">:</td>
            <td class="voucher-val" style="font-style: italic; color: #555;">{{ $payment->notes }}</td>
        </tr>
        @endif
    </table>

    <!-- 4-Kolom Otorisasi Standar Akuntansi Perusahaan -->
    <table class="signatures-table">
        <tr>
            <td class="sign-cell">
                <div class="sign-title">Dibuat Oleh:</div>
                <div class="sign-name">{{ $payment->createdBy->name ?? 'Kasir / Staf' }}</div>
            </td>
            <td class="sign-cell">
                <div class="sign-title">Diperiksa Oleh:</div>
                <div class="sign-name">Accounting Spv</div>
            </td>
            <td class="sign-cell">
                <div class="sign-title">Disetujui Oleh:</div>
                <div class="sign-name">Finance Manager</div>
            </td>
            <td class="sign-cell">
                <div class="sign-title">Diterima Oleh:</div>
                <div class="sign-name">{{ $payee?->name ?? 'Penerima' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini sah dan telah diposting otomatis ke Buku Besar Sistem Keuangan OPTIMA FINANCE.
    </div>

</body>
</html>
