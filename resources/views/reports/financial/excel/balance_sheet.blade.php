<table>
    <thead>
        <tr>
            <th colspan="3" style="font-size: 14pt; font-weight: bold;">{{ $companyName }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-size: 12pt; font-weight: bold;">LAPORAN POSISI KEUANGAN / NERACA (SAK EMKM)</th>
        </tr>
        <tr>
            <th colspan="3" style="font-style: italic;">Per Tanggal: {{ $data['as_of_date'] }}</th>
        </tr>
        <tr><th></th><th></th><th></th></tr>
        <tr style="background-color: #e2e8f0; font-weight: bold;">
            <th>Kode</th>
            <th>Uraian / Akun</th>
            <th style="text-align: right;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <!-- ASET LANCAR -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">ASET LANCAR</td>
            <td></td>
        </tr>
        @foreach ($data['current_assets'] as $ca)
            <tr>
                <td>{{ $ca['code'] }}</td>
                <td>{{ $ca['name'] }}</td>
                <td style="text-align: right;">{{ $ca['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold;">
            <td colspan="2">Total Aset Lancar</td>
            <td style="text-align: right;">{{ $data['total_current_assets'] }}</td>
        </tr>

        <!-- ASET TIDAK LANCAR -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">ASET TIDAK LANCAR (ASET TETAP)</td>
            <td></td>
        </tr>
        @foreach ($data['non_current_assets'] as $nca)
            <tr>
                <td>{{ $nca['code'] }}</td>
                <td>{{ $nca['name'] }}</td>
                <td style="text-align: right;">{{ $nca['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold;">
            <td colspan="2">Total Aset Tidak Lancar</td>
            <td style="text-align: right;">{{ $data['total_non_current_assets'] }}</td>
        </tr>

        <!-- TOTAL ASET -->
        <tr style="font-weight: bold; background-color: #dbeafe;">
            <td colspan="2">TOTAL ASET</td>
            <td style="text-align: right;">{{ $data['total_assets'] }}</td>
        </tr>

        <tr><th></th><th></th><th></th></tr>

        <!-- LIABILITAS -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">LIABILITAS (UTANG JANGKA PENDEK)</td>
            <td></td>
        </tr>
        @foreach ($data['current_liabilities'] as $cl)
            <tr>
                <td>{{ $cl['code'] }}</td>
                <td>{{ $cl['name'] }}</td>
                <td style="text-align: right;">{{ $cl['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold;">
            <td colspan="2">Total Liabilitas</td>
            <td style="text-align: right;">{{ $data['total_liabilities'] }}</td>
        </tr>

        <!-- EKUITAS -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">EKUITAS PEMILIK</td>
            <td></td>
        </tr>
        @foreach ($data['equities'] as $eq)
            <tr>
                <td>{{ $eq['code'] }}</td>
                <td>{{ $eq['name'] }}</td>
                <td style="text-align: right;">{{ $eq['amount'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="font-style: italic;">Laba Periode Berjalan</td>
            <td style="text-align: right;">{{ $data['current_period_earnings'] }}</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="2">Total Ekuitas</td>
            <td style="text-align: right;">{{ $data['total_equity'] }}</td>
        </tr>

        <!-- TOTAL LIABILITAS & EKUITAS -->
        <tr style="font-weight: bold; background-color: #dcfce7;">
            <td colspan="2">TOTAL LIABILITAS &amp; EKUITAS</td>
            <td style="text-align: right;">{{ $data['total_liabilities_and_equity'] }}</td>
        </tr>
    </tbody>
</table>
