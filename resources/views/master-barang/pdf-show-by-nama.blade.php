<!DOCTYPE html>
<html style="text-transform: uppercase;">
<head>
    <meta charset="utf-8">
    <title>Master Barang - {{ $nama_barang }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h3 { text-align: center; margin-bottom: 5px; }
        p.subtitle { text-align: center; margin-top: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #17a2b8; color: white; padding: 6px; text-align: center; }
        td { padding: 5px 6px; border: 1px solid #ddd; vertical-align: top; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-menipis { background-color: #dc3545; color: white; padding: 2px 5px; border-radius: 3px; font-size: 10px; }
        .badge-aman { color: #28a745; font-weight: bold; }
        .item-foto { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>

    <h3>Daftar Batch — {{ strtoupper($nama_barang) }}</h3>
    <p class="subtitle">Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">Kode Barang</th>
                <th width="8%">Foto</th>
                <th width="10%">Gudang</th>
                <th width="7%">Qty</th>
                <th width="8%">Satuan</th>
                <th width="9%">Qty Satuan</th>
                <th width="9%">Stok Saat Ini</th>
                <th width="12%">Harga Jual</th>
                <th width="13%">Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $index => $barang)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $barang->kode_barang }}</td>
                <td class="text-center">
                    @if ($barang->foto)
                        <img src="{{ storage_path('app/public/' . $barang->foto) }}" class="item-foto">
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ str_replace('_', ' ', ucwords($barang->gudang ?? '-')) }}</td>
                <td class="text-center">{{ $barang->qty }}</td>
                <td class="text-center">{{ $barang->satuan }}</td>
                <td class="text-center">{{ number_format($barang->qty_satuan) }}</td>
                <td class="text-center">
                    @if ($barang->stok_saat_ini <= 5)
                        <span class="badge-menipis">{{ number_format($barang->stok_saat_ini) }} Pcs</span>
                    @else
                        <span class="badge-aman">{{ number_format($barang->stok_saat_ini) }} Pcs</span>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($barang->tanggal_masuk)->format('d-m-Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data batch</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>