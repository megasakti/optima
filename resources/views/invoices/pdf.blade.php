<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FAKTUR INVOICE - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 28px 35px 28px 35px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333333;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 3px;
        }
        .company-sub {
            font-size: 10px;
            color: #555555;
            line-height: 1.3;
        }
        .invoice-title {
            text-align: right;
            vertical-align: top;
        }
        .invoice-title h1 {
            font-size: 22px;
            color: #1e3a8a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-meta-number {
            font-size: 12px;
            font-weight: bold;
            color: #444444;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .bill-to-box {
            width: 55%;
            vertical-align: top;
        }
        .meta-box {
            width: 45%;
            vertical-align: top;
            text-align: right;
        }
        .section-heading {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            color: #666666;
            margin-bottom: 4px;
        }
        .customer-name {
            font-size: 13px;
            font-weight: bold;
            color: #111111;
            margin-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            padding: 7px 8px;
            border: 1px solid #1e3a8a;
        }
        .items-table td {
            padding: 7px 8px;
            border: 1px solid #dddddd;
            font-size: 10.5px;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f9fbfd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 10.5px;
        }
        .totals-table .grand-total-row td {
            background-color: #f0f4f8;
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            border-top: 2px solid #1e3a8a;
            border-bottom: 2px solid #1e3a8a;
        }
        .payment-info {
            width: 50%;
            float: left;
            border: 1px solid #dcdcdc;
            background-color: #fafafa;
            padding: 10px 12px;
            border-radius: 4px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 30px;
        }
        .sign-box {
            width: 35%;
            text-align: center;
            vertical-align: top;
        }
        .sign-space {
            height: 60px;
        }
        .status-stamp {
            display: inline-block;
            border: 2px solid #16a34a;
            color: #16a34a;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 4px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        .footer-note {
            margin-top: 35px;
            border-top: 1px solid #eeeeee;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #888888;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="company-name">{{ $invoice->company->name ?? 'PT OPTIMA MITRA SERTIFIKASI' }}</div>
                <div class="company-sub">
                    Lembaga Pelatihan Kerja & Tempat Uji Kompetensi (TUK) BNSP Terlisensi<br>
                    Jl. Pemuda No. 128, Rawamangun, Jakarta Timur 13220<br>
                    Email: finance@optimatraining.co.id | NPWP: 01.234.567.8-001.000
                </div>
            </td>
            <td class="invoice-title">
                <h1>FAKTUR TAGIHAN</h1>
                <div class="invoice-meta-number">{{ $invoice->invoice_number }}</div>
                @if ($invoice->status === 'paid')
                    <div class="status-stamp">LUNAS / PAID</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Info Klien & Detail Faktur -->
    <table class="info-table">
        <tr>
            <td class="bill-to-box">
                <div class="section-heading">TAGIHAN KEPADA:</div>
                <div class="customer-name">{{ $invoice->customer->name }}</div>
                <div style="color: #444444; line-height: 1.3;">
                    {{ $invoice->customer->address ?? 'Indonesia' }}<br>
                    @if ($invoice->customer->pic_name)
                        UP: {{ $invoice->customer->pic_name }} ({{ $invoice->customer->pic_phone ?? '-' }})<br>
                    @endif
                    Email: {{ $invoice->customer->email ?? '-' }}<br>
                    NPWP: {{ $invoice->customer->npwp ?? '-' }}
                </div>
            </td>
            <td class="meta-box">
                <table style="width: 100%; text-align: right;">
                    <tr>
                        <td style="color: #666; font-size: 10px;">Tanggal Faktur:</td>
                        <td style="font-weight: bold; width: 45%;">{{ $invoice->invoice_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #666; font-size: 10px;">Jatuh Tempo:</td>
                        <td style="font-weight: bold; color: #b91c1c;">{{ $invoice->due_date->format('d F Y') }}</td>
                    </tr>
                    @if ($invoice->po_number)
                    <tr>
                        <td style="color: #666; font-size: 10px;">Nomor PO:</td>
                        <td style="font-weight: bold;">{{ $invoice->po_number }}</td>
                    </tr>
                    @endif
                    @if ($invoice->batch)
                    <tr>
                        <td style="color: #666; font-size: 10px;">Batch Pelatihan:</td>
                        <td style="font-weight: bold;">{{ $invoice->batch->batch_code }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Jasa / Item -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 50%;">Deskripsi Layanan & Program</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 17%;" class="text-right">Harga Satuan</th>
                <th style="width: 18%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if ($item->item_type === 'certification')
                            <div style="font-size: 9px; color: #0284c7;">Uji Sertifikasi Kompetensi BNSP</div>
                        @elseif ($item->item_type === 'training')
                            <div style="font-size: 9px; color: #16a34a;">Program Pelatihan & Workshop</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }} Pax</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Rincian Total & Perhitungan Pajak -->
    <table style="width: 100%;">
        <tr>
            <!-- Instruksi Pembayaran Bank -->
            <td style="width: 52%; vertical-align: top;">
                <div class="payment-info">
                    <strong style="color: #1e3a8a; font-size: 10px;">INSTRUKSI PEMBAYARAN BANK:</strong>
                    <div style="margin-top: 4px; line-height: 1.4; font-size: 10px;">
                        Bank: <strong>Bank Central Asia (BCA)</strong><br>
                        No. Rekening: <strong>880-123-9988</strong><br>
                        Atas Nama: <strong>PT Optima Mitra Sertifikasi</strong><br>
                        Cabang: KCP Rawamangun, Jakarta
                    </div>
                    <div style="margin-top: 6px; font-size: 9px; color: #666;">
                        * Harap cantumkan nomor faktur <strong>{{ $invoice->invoice_number }}</strong> pada berita transfer.
                    </div>
                </div>
            </td>

            <!-- Tabel Kalkulasi Total -->
            <td style="width: 48%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td style="color: #666;">Subtotal Jasa:</td>
                        <td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if ((float) $invoice->tax_ppn_amount > 0)
                    <tr>
                        <td style="color: #666;">PPN ({{ $invoice->tax_ppn_rate }}%):</td>
                        <td class="text-right">Rp {{ number_format($invoice->tax_ppn_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if ((float) $invoice->down_payment_applied > 0)
                    <tr>
                        <td style="color: #b91c1c;">Potongan DP:</td>
                        <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($invoice->down_payment_applied, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td>TOTAL TAGIHAN:</td>
                        <td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if ((float) $invoice->paid_amount > 0)
                    <tr>
                        <td style="color: #16a34a;">Telah Dibayar:</td>
                        <td class="text-right" style="color: #16a34a;">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #b91c1c;">SISA TAGIHAN:</td>
                        <td class="text-right" style="font-weight: bold; color: #b91c1c;">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Resmi -->
    <table class="signatures-table">
        <tr>
            <td style="width: 60%;"></td>
            <td class="sign-box">
                <div style="font-size: 10px;">Jakarta, {{ $invoice->invoice_date->format('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 2px;">PT OPTIMA MITRA SERTIFIKASI</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Dr. H. Budi Santoso, S.E., M.M.</div>
                <div style="font-size: 9px; color: #666;">Direktur Keuangan & Operasional</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat dan divalidasi secara elektronik melalui Sistem Keuangan Terintegrasi OPTIMA FINANCE SAK EMKM.
    </div>

</body>
</html>
