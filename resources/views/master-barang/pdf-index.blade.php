<!DOCTYPE html>
<html style="text-transform: uppercase;">
<head>
    <meta charset="utf-8">
    <title>Laporan Master Barang</title>
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
    </style>
</head>
<body>

    <h3>Laporan Master Barang</h3>
    <p class="subtitle">Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang</th>
                <th width="12%">Jumlah Batch</th>
                <th width="12%">Total Stok</th>
                <th width="20%">Rentang Harga</th>
                <th width="15%">Tanggal Masuk Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-center">{{ $item->jumlah_batch }} Batch</td>
                <td class="text-center">
                    @if ($item->total_stok <= 5)
                        <span class="badge-menipis">{{ number_format($item->total_stok) }} Pcs</span>
                    @else
                        <span class="badge-aman">{{ number_format($item->total_stok) }} Pcs</span>
                    @endif
                </td>
                <td class="text-right">
                    @if ($item->harga_terendah == $item->harga_tertinggi)
                        Rp {{ number_format($item->harga_terendah, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($item->harga_terendah, 0, ',', '.') }} - Rp {{ number_format($item->harga_tertinggi, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($item->tanggal_masuk_terakhir)->format('d-m-Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data barang</td>
            </tr>
            @endforelse
        </tbody>
       {{-- <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>TOTAL KESELURUHAN</strong></td>
                <td class="text-center">
                    <strong>{{ $barangs->sum('jumlah_batch') }} Batch</strong>
                </td>
                <td class="text-center">
                    <strong>{{ number_format($barangs->sum('total_stok')) }} Pcs</strong>
                </td>
                <td class="text-right">
                    <strong>
                        @if ($barangs->count() > 0)
                            @php
                                $minHargaKeseluruhan = $barangs->min('harga_terendah');
                                $maxHargaKeseluruhan = $barangs->max('harga_tertinggi');
                            @endphp
                            
                            @if ($minHargaKeseluruhan == $maxHargaKeseluruhan)
                                Rp {{ number_format($minHargaKeseluruhan, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($minHargaKeseluruhan, 0, ',', '.') }} - Rp {{ number_format($maxHargaKeseluruhan, 0, ',', '.') }}
                            @endif
                        @else
                            -
                        @endif
                    </strong>
                </td>
                <td></td>
            </tr>
        </tfoot> --}}
    </table>

</body>
</html>