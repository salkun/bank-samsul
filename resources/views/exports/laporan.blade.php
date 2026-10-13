<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bank Sampah & Poin</title>
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

    <!-- 1. Ringkasan Poin -->
    <div class="section-title">1. Ringkasan Akumulasi Poin</div>
    <table>
        <tr>
            <th width="50%">Total Poin Sampah Masuk (Setoran)</th>
            <td class="text-right"><strong>{{ number_format($poin_sampah_masuk, 0, ',', '.') }} Poin</strong></td>
        </tr>
        <tr>
            <th>Total Poin Dipakai (Penukaran Barang)</th>
            <td class="text-right"><strong>{{ number_format($poin_dipakai, 0, ',', '.') }} Poin</strong></td>
        </tr>
        <tr>
            <th>Selisih Poin (Netto)</th>
            <td class="text-right"><strong>{{ number_format($poin_sampah_masuk - $poin_dipakai, 0, ',', '.') }} Poin</strong></td>
        </tr>
    </table>

    <!-- 2. Ringkasan Kategori Sampah -->
    <div class="section-title">2. Setoran per Kategori Utama</div>
    <table>
        <thead>
            <tr>
                <th>Kategori Utama</th>
                <th class="text-center">Total Berat (Kg)</th>
                <th class="text-right">Total Poin Sampah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($setoran_per_kategori as $item)
            <tr>
                <td>{{ $item->kategori }}</td>
                <td class="text-center">{{ number_format($item->total_kg, 2, ',', '.') }} Kg</td>
                <td class="text-right">{{ number_format($item->total_poin, 0, ',', '.') }} Poin</td>
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
                <th class="text-right">Total Poin Sampah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($berat_per_jenis as $item)
            <tr>
                <td>{{ $item->jenis }}</td>
                <td class="text-center">{{ number_format($item->total_kg, 2, ',', '.') }} Kg</td>
                <td class="text-right">{{ number_format($item->total_poin, 0, ',', '.') }} Poin</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 4. Transaksi Nasabah Tertinggi -->
    <div class="section-title">4. Aktivitas Setoran Nasabah</div>
    <table>
        <thead>
            <tr>
                <th>Nama Nasabah</th>
                <th class="text-center">Total Transaksi</th>
                <th class="text-center">Total Berat (Kg)</th>
                <th class="text-right">Total Poin Sampah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi_per_nasabah->take(10) as $item)
            <tr>
                <td>{{ $item->nasabah }}</td>
                <td class="text-center">{{ $item->total_transaksi }}</td>
                <td class="text-center">{{ number_format($item->total_berat, 2, ',', '.') }} Kg</td>
                <td class="text-right">{{ number_format($item->total_poin, 0, ',', '.') }} Poin</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 5. Rekapitulasi Penukaran Barang -->
    <div class="section-title">5. Rekapitulasi Penukaran Barang (Redeem)</div>
    <table>
        <thead>
            <tr>
                <th>Status Penukaran</th>
                <th class="text-center">Jumlah Pengajuan</th>
                <th class="text-right">Total Poin Dipakai</th>
            </tr>
        </thead>
        <tbody>
            @if(count($rekap_penukaran) > 0)
                @foreach($rekap_penukaran as $item)
                <tr>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td class="text-center">{{ $item->total }}</td>
                    <td class="text-right">{{ number_format($item->total_poin, 0, ',', '.') }} Poin</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">Belum ada aktivitas penukaran barang pada periode ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- 6. Survey Lingkungan -->
    <div class="section-title">6. Rekapitulasi Survey Lingkungan</div>
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
