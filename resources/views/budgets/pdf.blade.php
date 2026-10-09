<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RENCANA ANGGARAN BIAYA - {{ $budget->budget_number }}</title>
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
            margin-bottom: 18px;
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
        .doc-title {
            text-align: right;
            vertical-align: top;
        }
        .doc-title h1 {
            font-size: 20px;
            color: #1e3a8a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-meta-number {
            font-size: 12px;
            font-weight: bold;
            color: #444444;
        }
        .status-stamp {
            display: inline-block;
            border: 2px solid #16a34a;
            color: #16a34a;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 4px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 18px;
            border: 1px solid #dcdcdc;
            background-color: #fcfcfc;
            padding: 8px 12px;
            border-radius: 4px;
        }
        .info-table td {
            vertical-align: top;
            padding: 3px 6px;
            font-size: 10.5px;
        }
        .info-label {
            color: #666666;
            width: 25%;
        }
        .info-val {
            font-weight: bold;
            color: #222222;
            width: 25%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
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
            padding: 6px 8px;
            border: 1px solid #dddddd;
            font-size: 10px;
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
            width: 55%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 11px;
        }
        .totals-table .grand-row td {
            background-color: #f0f4f8;
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            border-top: 2px solid #1e3a8a;
            border-bottom: 2px solid #1e3a8a;
        }
        .signatures-table {
            width: 100%;
            margin-top: 25px;
        }
        .sign-box {
            width: 45%;
            text-align: center;
            vertical-align: top;
        }
        .sign-space {
            height: 55px;
        }
        .footer-note {
            margin-top: 30px;
            border-top: 1px solid #eeeeee;
            padding-top: 6px;
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
            <td style="width: 55%; vertical-align: top;">
                <div class="company-name">{{ $budget->company->name ?? 'PT OPTIMA MITRA SERTIFIKASI' }}</div>
                <div class="company-sub">
                    Lembaga Pelatihan Kerja & Tempat Uji Kompetensi (TUK) BNSP Terlisensi<br>
                    Jl. Pemuda No. 128, Rawamangun, Jakarta Timur 13220<br>
                    Email: finance@optimatraining.co.id | NPWP: 01.234.567.8-001.000
                </div>
            </td>
            <td class="doc-title">
                <h1>RENCANA ANGGARAN BIAYA (RAB)</h1>
                <div class="doc-meta-number">{{ $budget->budget_number }}</div>
                @if ($budget->status === 'approved')
                    <div class="status-stamp">DISETUJUI / APPROVED</div>
                @else
                    <div style="font-size: 10px; color: #b45309; font-weight: bold; margin-top: 4px;">
                        STATUS: {{ strtoupper($budget->status) }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Info Angkatan / Batch -->
    <table class="info-table">
        <tr>
            <td class="info-label">Kode & Nama Batch:</td>
            <td class="info-val">{{ $budget->batch->batch_code }} - {{ $budget->batch->name }}</td>
            <td class="info-label">Target Peserta:</td>
            <td class="info-val">{{ number_format($budget->target_participants, 0) }} Pax</td>
        </tr>
        <tr>
            <td class="info-label">Program Sertifikasi:</td>
            <td class="info-val">{{ $budget->batch->program->name }}</td>
            <td class="info-label">Harga Tiket / Pax:</td>
            <td class="info-val">Rp {{ number_format($budget->target_revenue_per_pax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Jadwal Pelatihan:</td>
            <td class="info-val">{{ $budget->batch->start_date->format('d/m/Y') }} s/d {{ $budget->batch->end_date->format('d/m/Y') }}</td>
            <td class="info-label">Target Omset Batch:</td>
            <td class="info-val" style="color: #16a34a;">Rp {{ number_format($budget->total_revenue_budget, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Mitra LSP Pelaksana:</td>
            <td class="info-val">{{ $budget->batch->lsp->name ?? 'LSP Mitra' }}</td>
            <td class="info-label">Instruktur Utama:</td>
            <td class="info-val">{{ $budget->batch->trainer->name ?? '-' }}</td>
        </tr>
    </table>

    <!-- Rincian Pos Biaya Anggaran -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 18%;">Kategori</th>
                <th style="width: 32%;">Deskripsi Pos Pengeluaran</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 10%;" class="text-center">Satuan</th>
                <th style="width: 12%;" class="text-right">Harga Satuan</th>
                <th style="width: 13%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($budget->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold; color: #1e3a8a;">{{ strtoupper($item->category) }}</td>
                    <td>
                        {{ $item->description }}
                        @if ($item->account)
                            <div style="font-size: 8.5px; color: #666;">(Pos Akun: {{ $item->account->code }} - {{ $item->account->name }})</div>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Ringkasan Anggaran & Proyeksi Laba Kotor -->
    <table style="width: 100%;">
        <tr>
            <td style="width: 45%; vertical-align: top;">
                @if ($budget->notes)
                <div style="border: 1px solid #ddd; background: #fafafa; padding: 8px 10px; font-size: 9.5px; border-radius: 4px;">
                    <strong>Catatan & Asumsi:</strong><br>
                    {{ $budget->notes }}
                </div>
                @endif
            </td>
            <td style="width: 55%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td style="color: #666;">Target Penjualan (Revenue):</td>
                        <td class="text-right" style="font-weight: bold; color: #16a34a;">
                            Rp {{ number_format($budget->total_revenue_budget, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Total Anggaran Biaya (HPP):</td>
                        <td class="text-right" style="font-weight: bold; color: #dc2626;">
                            Rp {{ number_format($budget->total_cost_budget, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="grand-row">
                        <td>PROYEKSI LABA KOTOR:</td>
                        <td class="text-right">
                            Rp {{ number_format($budget->projected_gross_margin, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #333;">Margin Laba Kotor (%):</td>
                        <td class="text-right" style="font-weight: bold; font-size: 12px; color: {{ $budget->projected_margin_percentage >= 30 ? '#16a34a' : '#b45309' }};">
                            {{ number_format($budget->projected_margin_percentage, 1) }}%
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Resmi Dokumen RAB -->
    <table class="signatures-table">
        <tr>
            <td class="sign-box">
                <div>Disusun Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Koordinator Operasional / Pelatihan</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $budget->createdBy->name ?? 'Staf Operasional' }}</div>
                <div style="font-size: 9px; color: #666;">Divisi Pelatihan & Sertifikasi</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sign-box">
                <div>Disetujui Oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Finance & Accounting Manager</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $budget->approvedBy->name ?? 'Finance Manager' }}</div>
                <div style="font-size: 9px; color: #666;">Divisi Keuangan & Anggaran</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen Rencana Anggaran Biaya (RAB) resmi PT OPTIMA MITRA SERTIFIKASI. Dicetak secara otomatis pada {{ date('d F Y H:i') }}.
    </div>

</body>
</html>
