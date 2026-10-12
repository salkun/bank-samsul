<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bank Sampah</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #065f46; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #065f46; color: white; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; color: #065f46; border-bottom: 2px solid #065f46; padding-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Rekapitulasi BankSamsul</h2>
        <p>{{ $periode }}</p>
    </div>

    <!-- 1. Ringkasan Saldo -->
    <div class="section-title">1. Ringkasan Keuangan</div>
    <table>
        <tr>
            <th width="50%">Total Saldo Masuk (Setoran)</th>
            <td class="text-right">Rp {{ number_format($saldo_masuk, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Saldo Keluar (Penarikan)</th>
            <td class="text-right">Rp {{ number_format($saldo_keluar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Netto</th>
            <td class="text-right"><strong>Rp {{ number_format($saldo_masuk - $saldo_keluar, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <!-- 2. Ringkasan Kategori Sampah -->
    <div class="section-title">2. Setoran per Kategori Utama</div>
    <table>
        <thead>
            <tr>
                <th>Kategori Utama</th>
                <th class="text-center">Total Berat (Kg)</th>
                <th class="text-right">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($setoran_per_kategori as $item)
            <tr>
                <td>{{ $item->kategori }}</td>
                <td class="text-center">{{ number_format($item->total_kg, 2, ',', '.') }} Kg</td>
                <td class="text-right">Rp {{ number_format($item->total_rp, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 3. Rincian per Jenis Sampah -->
    <div class="section-title">3. Rincian per Jenis Sampah</div>
    <table>
        <thead>
            <tr>
                <th>Jenis Sampah</th>
                <th class="text-center">Total Berat (Kg)</th>
                <th class="text-right">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($berat_per_jenis as $item)
            <tr>
                <td>{{ $item->jenis }}</td>
                <td class="text-center">{{ number_format($item->total_kg, 2, ',', '.') }} Kg</td>
                <td class="text-right">Rp {{ number_format($item->total_rp, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 4. Transaksi Nasabah Tertinggi -->
    <div class="section-title">4. Aktivitas Nasabah (Tertinggi)</div>
    <table>
        <thead>
            <tr>
                <th>Nama Nasabah</th>
                <th class="text-center">Total Transaksi</th>
                <th class="text-right">Total Setoran (Rp)</th>
                <th class="text-right">Total Penarikan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi_per_nasabah->take(10) as $item)
            <tr>
                <td>{{ $item->nasabah }}</td>
                <td class="text-center">{{ $item->total_transaksi }}</td>
                <td class="text-right">Rp {{ number_format($item->total_masuk, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total_keluar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 5. Survey Lingkungan -->
    <div class="section-title">5. Rekapitulasi Survey Lingkungan</div>
    <table>
        <thead>
            <tr>
                <th>Kategori Survey</th>
                <th>Status Penilaian</th>
                <th class="text-center">Jumlah Kelas (Nasabah)</th>
            </tr>
        </thead>
        <tbody>
            @if(count($survey_lingkungan) > 0)
                @foreach($survey_lingkungan as $item)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $item->kategori)) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                    <td class="text-center">{{ $item->total }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">Belum ada data survey lingkungan di periode ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
