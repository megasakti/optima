<table>
    <thead>
        <tr>
            <th colspan="3" style="font-size: 14pt; font-weight: bold;">{{ $companyName }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-size: 12pt; font-weight: bold;">LAPORAN LABA RUGI (SAK EMKM)</th>
        </tr>
        <tr>
            <th colspan="3" style="font-style: italic;">Periode: {{ $data['start_date'] }} s/d {{ $data['end_date'] }}</th>
        </tr>
        <tr><th></th><th></th><th></th></tr>
        <tr style="background-color: #e2e8f0; font-weight: bold;">
            <th>Kode Akun</th>
            <th>Keterangan / Pos Akun</th>
            <th style="text-align: right;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <!-- PENDAPATAN -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">PENDAPATAN USAHA</td>
            <td></td>
        </tr>
        @foreach ($data['revenues'] as $rev)
            <tr>
                <td>{{ $rev['code'] }}</td>
                <td>{{ $rev['name'] }}</td>
                <td style="text-align: right;">{{ $rev['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold;">
            <td colspan="2">Total Pendapatan Usaha</td>
            <td style="text-align: right;">{{ $data['total_revenue'] }}</td>
        </tr>

        <!-- BEBAN POKOK -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">BEBAN POKOK PELATIHAN &amp; SERTIFIKASI (HPP)</td>
            <td></td>
        </tr>
        @foreach ($data['cogs'] as $c)
            <tr>
                <td>{{ $c['code'] }}</td>
                <td>{{ $c['name'] }}</td>
                <td style="text-align: right;">{{ $c['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold;">
            <td colspan="2">Total Beban Pokok Pelatihan</td>
            <td style="text-align: right;">{{ $data['total_cogs'] }}</td>
        </tr>

        <!-- LABA KOTOR -->
        <tr style="font-weight: bold; background-color: #dbeafe;">
            <td colspan="2">LABA KOTOR (GROSS PROFIT)</td>
            <td style="text-align: right;">{{ $data['gross_profit'] }}</td>
        </tr>

        <!-- OPERASIONAL -->
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="2">BEBAN OPERASIONAL &amp; ADMINISTRASI UMUM</td>
            <td></td>
        </tr>
        @foreach ($data['operating_expenses'] as $opex)
            <tr>
                <td>{{ $opex['code'] }}</td>
                <td>{{ $opex['name'] }}</td>
                <td style="text-align: right;">{{ $opex['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold;">
            <td colspan="2">Total Beban Operasional</td>
            <td style="text-align: right;">{{ $data['total_operating_expenses'] }}</td>
        </tr>

        <!-- LABA OPERASIONAL -->
        <tr style="font-weight: bold;">
            <td colspan="2">LABA OPERASIONAL</td>
            <td style="text-align: right;">{{ $data['operating_profit'] }}</td>
        </tr>

        <!-- LABA BERSIH -->
        <tr style="font-weight: bold; background-color: #dcfce7;">
            <td colspan="2">LABA BERSIH PERIODE BERJALAN</td>
            <td style="text-align: right;">{{ $data['net_profit'] }}</td>
        </tr>
    </tbody>
</table>
