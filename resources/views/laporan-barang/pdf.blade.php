<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pergerakan Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h2, h4 { margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 15px; }
        .ringkasan { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .ringkasan td { padding: 6px; border: 1px solid #ccc; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 5px; }
        table.data th { background-color: #eee; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-masuk { color: #28a745; font-weight: bold; }
        .badge-keluar { color: #dc3545; font-weight: bold; }
        .badge-paket { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN PERGERAKAN BARANG</h2>
        <h4>PT. Manggala Trans Utama</h4>
        <p>
            Periode: {{ $periodeLabel }}
            @if ($namaBarang)
                | Nama Barang: {{ $namaBarang }}
            @endif
        </p>
    </div>

    <table class="ringkasan">
        <tr>
            <td><strong>Total Qty Masuk</strong><br>{{ number_format($ringkasan['total_qty_masuk']) }} Pcs</td>
            <td><strong>Total Qty Keluar</strong><br>{{ number_format($ringkasan['total_qty_keluar']) }} Pcs</td>
            <td><strong>Total Uang Keluar (Beli)</strong><br>Rp {{ number_format($ringkasan['total_uang_keluar'], 0, ',', '.') }}</td>
            <td><strong>Total Uang Masuk (Jual)</strong><br>Rp {{ number_format($ringkasan['total_uang_masuk'], 0, ',', '.') }}
                <br><small>*di luar transaksi Paket Service</small>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal</th>
                <th width="17%">Nama Barang</th>
                <th width="10%">Kode Barang</th>
                <th width="12%">Tipe</th>
                <th width="8%">Qty</th>
                <th width="12%">Subtotal</th>
                <th width="27%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pergerakan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') : '-' }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td class="text-center">
                    @if ($item->tipe === 'masuk')
                        <span class="badge-masuk">{{ $item->tipe_label }}</span>
                    @elseif ($item->tipe === 'keluar_per_item')
                        <span class="badge-keluar">{{ $item->tipe_label }}</span>
                    @else
                        <span class="badge-paket">{{ $item->tipe_label }}</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->qty) }} {{ $item->satuan }}</td>
                <td class="text-right">
                    {{ is_null($item->subtotal) ? '-' : 'Rp ' . number_format($item->subtotal, 0, ',', '.') }}
                </td>
                <td>{{ $item->keterangan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada pergerakan barang pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 15px; font-size: 10px; color: #777;">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}
    </p>

</body>
</html>