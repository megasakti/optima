<table>
    <thead>
        <tr>
            <th colspan="3" style="font-size: 14pt; font-weight: bold;">{{ $companyName }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-size: 12pt; font-weight: bold;">LAPORAN ARUS KAS (SAK EMKM)</th>
        </tr>
        <tr>
            <th colspan="3" style="font-style: italic;">Periode: {{ $data['start_date'] }} s/d {{ $data['end_date'] }}</th>
        </tr>
        <tr><th></th><th></th><th></th></tr>
        <tr style="background-color: #e2e8f0; font-weight: bold;">
            <th colspan="2">Aktivitas / Uraian Arus Kas</th>
            <th style="text-align: right;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <!-- OPERASI -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">ARUS KAS DARI AKTIVITAS OPERASI</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2">Penerimaan Kas dari Pelanggan &amp; Penjualan Jasa Pelatihan</td>
            <td style="text-align: right;">{{ $data['operating_receipts'] }}</td>
        </tr>
        <tr>
            <td colspan="2">Pembayaran Kas kepada Mitra LSP &amp; Pemasok Bahan Pelatihan</td>
            <td style="text-align: right;">-{{ $data['operating_payments'] }}</td>
        </tr>
        <tr>
            <td colspan="2">Pembayaran Kas untuk Beban Operasional, Gaji &amp; Sewa</td>
            <td style="text-align: right;">-{{ $data['operating_other_payments'] }}</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="2">Arus Kas Bersih dari Aktivitas Operasi</td>
            <td style="text-align: right;">{{ $data['net_operating_cash'] }}</td>
        </tr>

        <!-- INVESTASI -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">ARUS KAS DARI AKTIVITAS INVESTASI</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2">Pembelian Peralatan Kantor / Aset Tetap</td>
            <td style="text-align: right;">0</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="2">Arus Kas Bersih dari Aktivitas Investasi</td>
            <td style="text-align: right;">0</td>
        </tr>

        <!-- PENDANAAN -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">ARUS KAS DARI AKTIVITAS PENDANAAN</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2">Penyetoran Modal Pemilik / (Prive)</td>
            <td style="text-align: right;">0</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="2">Arus Kas Bersih dari Aktivitas Pendanaan</td>
            <td style="text-align: right;">0</td>
        </tr>

        <!-- REKONSILIASI -->
        <tr style="font-weight: bold;">
            <td colspan="2">KENAIKAN / (PENURUNAN) BERSIH KAS &amp; SETARA KAS</td>
            <td style="text-align: right;">{{ $data['net_change_in_cash'] }}</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="2">SALDO KAS &amp; SETARA KAS PADA AWAL PERIODE</td>
            <td style="text-align: right;">{{ $data['beginning_cash'] }}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #dbeafe;">
            <td colspan="2">SALDO KAS &amp; SETARA KAS PADA AKHIR PERIODE</td>
            <td style="text-align: right;">{{ $data['ending_cash'] }}</td>
        </tr>
    </tbody>
</table>
