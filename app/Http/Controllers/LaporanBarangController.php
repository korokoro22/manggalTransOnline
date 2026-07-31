<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Barang_masuk_detail;
use App\Models\Transaksi_keluar_detail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LaporanBarangController extends Controller
{
    /**
     * Halaman utama laporan pergerakan barang.
     * Menggabungkan 3 sumber data: barang masuk, transaksi keluar per_item,
     * dan transaksi keluar via paket_service — menjadi satu tabel pergerakan.
     */
    public function index(Request $request)
    {
        // ===== 1. Tentukan filter tanggal =====
        // Jika user belum submit filter apapun (belum ada query string sama sekali),
        // default ke bulan & tahun berjalan.
        $adaFilterManual = $request->hasAny(['tanggal', 'bulan', 'tahun', 'nama_barang', 'tipe', 'sub_tipe_keluar']);

        $tanggal = $request->input('tanggal');
        $bulan   = $request->input('bulan');
        $tahun   = $request->input('tahun');

        if (!$adaFilterManual) {
            $bulan = now()->month;
            $tahun = now()->year;
        }

        $namaBarang     = $request->input('nama_barang');
        $tipe           = $request->input('tipe', 'semua'); // masuk | keluar | semua
        $subTipeKeluar  = $request->input('sub_tipe_keluar', 'semua'); // per_item | paket_service | semua

        // ===== 2. Query A — Barang Masuk =====
        $dataMasuk = collect();
        if ($tipe === 'semua' || $tipe === 'masuk') {
            $dataMasuk = Barang_masuk_detail::with(['barang', 'barangMasuk'])
                ->whereHas('barang', function ($q) use ($namaBarang) {
                    if ($namaBarang) {
                        $q->where('nama_barang', 'like', '%' . $namaBarang . '%');
                    }
                })
                ->whereHas('barangMasuk', function ($q) use ($tanggal, $bulan, $tahun) {
                    if ($tanggal) {
                        $q->whereDate('tanggal_masuk', $tanggal);
                    } else {
                        if ($bulan) $q->whereMonth('tanggal_masuk', $bulan);
                        if ($tahun) $q->whereYear('tanggal_masuk', $tahun);
                    }
                })
                ->get()
                ->map(function ($d) {
                    return (object) [
                        'tanggal'     => $d->barangMasuk->tanggal_masuk ?? null,
                        'nama_barang' => $d->barang->nama_barang ?? $d->nama_barang,
                        'kode_barang' => $d->barang->kode_barang ?? '-',
                        'tipe'        => 'masuk',
                        'tipe_label'  => 'Masuk',
                        'qty'         => $d->qty_satuan,
                        'satuan'      => $d->satuan,
                        'subtotal'    => $d->subtotal,
                        'keterangan'  => 'No. Invoice: ' . ($d->barangMasuk->no_invoice ?? '-'),
                        'sumber_id'   => $d->id,
                    ];
                });
        }

        // ===== 3. Query B — Keluar Per Item =====
        $dataKeluarPerItem = collect();
        if (($tipe === 'semua' || $tipe === 'keluar') && ($subTipeKeluar === 'semua' || $subTipeKeluar === 'per_item')) {
            $dataKeluarPerItem = Transaksi_keluar_detail::with(['barang', 'transaksiKeluar.bus'])
                ->where('tipe', 'per_item')
                ->whereHas('barang', function ($q) use ($namaBarang) {
                    if ($namaBarang) {
                        $q->where('nama_barang', 'like', '%' . $namaBarang . '%');
                    }
                })
                ->whereHas('transaksiKeluar', function ($q) use ($tanggal, $bulan, $tahun) {
                    if ($tanggal) {
                        $q->whereDate('tanggal', $tanggal);
                    } else {
                        if ($bulan) $q->whereMonth('tanggal', $bulan);
                        if ($tahun) $q->whereYear('tanggal', $tahun);
                    }
                })
                ->get()
                ->map(function ($d) {
                    return (object) [
                        'tanggal'     => $d->transaksiKeluar->tanggal ?? null,
                        'nama_barang' => $d->barang->nama_barang ?? $d->nama_item,
                        'kode_barang' => $d->barang->kode_barang ?? '-',
                        'tipe'        => 'keluar_per_item',
                        'tipe_label'  => 'Keluar (Per Item)',
                        'qty'         => $d->qty,
                        'satuan'      => $d->satuan,
                        'subtotal'    => $d->subtotal,
                        'keterangan'  => 'Bus: ' . ($d->transaksiKeluar->bus->nama_bus ?? '-'),
                        'sumber_id'   => $d->id,
                    ];
                });
        }

        // ===== 4. Query C — Keluar via Paket Service =====
        // Satu baris transaksi_keluar_detail (tipe paket_service) bisa mengandung
        // banyak barang di dalam paketnya, jadi hasilnya di-flatMap.
        $dataKeluarPaket = collect();
        if (($tipe === 'semua' || $tipe === 'keluar') && ($subTipeKeluar === 'semua' || $subTipeKeluar === 'paket_service')) {
            $dataKeluarPaket = Transaksi_keluar_detail::with(['paketService.paketServiceItem.barang', 'transaksiKeluar.bus'])
                ->where('tipe', 'paket_service')
                ->whereHas('transaksiKeluar', function ($q) use ($tanggal, $bulan, $tahun) {
                    if ($tanggal) {
                        $q->whereDate('tanggal', $tanggal);
                    } else {
                        if ($bulan) $q->whereMonth('tanggal', $bulan);
                        if ($tahun) $q->whereYear('tanggal', $tahun);
                    }
                })
                ->get()
                ->flatMap(function ($d) use ($namaBarang) {
                    if (!$d->paketService) return collect();

                    return $d->paketService->paketServiceItem
                        ->filter(function ($item) use ($namaBarang) {
                            if (!$namaBarang) return true;
                            return $item->barang && stripos($item->barang->nama_barang, $namaBarang) !== false;
                        })
                        ->map(function ($item) use ($d) {
                            return (object) [
                                'tanggal'     => $d->transaksiKeluar->tanggal ?? null,
                                'nama_barang' => $item->barang->nama_barang ?? '-',
                                'kode_barang' => $item->barang->kode_barang ?? '-',
                                'tipe'        => 'keluar_paket_service',
                                'tipe_label'  => 'Keluar (Paket Service)',
                                'qty'         => $item->qty,
                                'satuan'      => $item->barang->satuan ?? '-',
                                'subtotal'    => null, // sengaja null — subtotal milik paket, bukan per barang
                                'keterangan'  => 'Paket: ' . ($d->paketService->nama_paket ?? '-')
                                                . ' | Bus: ' . ($d->transaksiKeluar->bus->nama_bus ?? '-'),
                                'sumber_id'   => $d->id . '-' . $item->id,
                            ];
                        });
                });
        }

        // ===== 5. Gabungkan semua, urutkan by tanggal terbaru =====
        $semua = $dataMasuk
            ->concat($dataKeluarPerItem)
            ->concat($dataKeluarPaket)
            ->sortByDesc('tanggal')
            ->values();

        // ===== 6. Hitung ringkasan (dari SEMUA data yang match filter, sebelum pagination) =====
        $ringkasan = [
            'total_qty_masuk'   => $semua->where('tipe', 'masuk')->sum('qty'),
            'total_qty_keluar'  => $semua->whereIn('tipe', ['keluar_per_item', 'keluar_paket_service'])->sum('qty'),
            'total_uang_keluar' => $semua->where('tipe', 'masuk')->sum('subtotal'),
            'total_uang_masuk'  => $semua->where('tipe', 'keluar_per_item')->sum('subtotal'), // paket_service dikecualikan
        ];

        // ===== 7. Pagination manual =====
        $perPage     = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items       = $semua->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $pergerakan = new LengthAwarePaginator(
            $items,
            $semua->count(),
            $perPage,
            $currentPage,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('laporan-barang.index', [
            'pergerakan'     => $pergerakan,
            'ringkasan'      => $ringkasan,
            'namaBarang'     => $namaBarang,
            'tanggal'        => $tanggal,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'tipe'           => $tipe,
            'subTipeKeluar'  => $subTipeKeluar,
        ]);
    }

    /**
     * Export PDF untuk data yang sedang difilter (tanpa pagination — semua baris).
     */
    public function exportPdf(Request $request)
    {
        $adaFilterManual = $request->hasAny(['tanggal', 'bulan', 'tahun', 'nama_barang', 'tipe', 'sub_tipe_keluar']);

        $tanggal = $request->input('tanggal');
        $bulan   = $request->input('bulan');
        $tahun   = $request->input('tahun');

        if (!$adaFilterManual) {
            $bulan = now()->month;
            $tahun = now()->year;
        }

        $namaBarang    = $request->input('nama_barang');
        $tipe          = $request->input('tipe', 'semua');
        $subTipeKeluar = $request->input('sub_tipe_keluar', 'semua');

        // ---- Query sama persis seperti index(), disatukan lewat method private di bawah ----
        $semua = $this->ambilSemuaPergerakan($namaBarang, $tanggal, $bulan, $tahun, $tipe, $subTipeKeluar);

        $ringkasan = [
            'total_qty_masuk'   => $semua->where('tipe', 'masuk')->sum('qty'),
            'total_qty_keluar'  => $semua->whereIn('tipe', ['keluar_per_item', 'keluar_paket_service'])->sum('qty'),
            'total_uang_keluar' => $semua->where('tipe', 'masuk')->sum('subtotal'),
            'total_uang_masuk'  => $semua->where('tipe', 'keluar_per_item')->sum('subtotal'),
        ];

        $periodeLabel = $this->buatLabelPeriode($tanggal, $bulan, $tahun);

        $pdf = Pdf::loadView('laporan-barang.pdf', [
            'pergerakan'   => $semua,
            'ringkasan'    => $ringkasan,
            'namaBarang'   => $namaBarang,
            'periodeLabel' => $periodeLabel,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-barang-' . now()->format('d-m-Y-His') . '.pdf');
    }

    /**
     * Helper: ambil & gabungkan 3 sumber data pergerakan (dipakai index() dan exportPdf()).
     */
    private function ambilSemuaPergerakan($namaBarang, $tanggal, $bulan, $tahun, $tipe, $subTipeKeluar): Collection
    {
        $dataMasuk = collect();
        if ($tipe === 'semua' || $tipe === 'masuk') {
            $dataMasuk = Barang_masuk_detail::with(['barang', 'barangMasuk'])
                ->whereHas('barang', function ($q) use ($namaBarang) {
                    if ($namaBarang) $q->where('nama_barang', 'like', '%' . $namaBarang . '%');
                })
                ->whereHas('barangMasuk', function ($q) use ($tanggal, $bulan, $tahun) {
                    if ($tanggal) {
                        $q->whereDate('tanggal_masuk', $tanggal);
                    } else {
                        if ($bulan) $q->whereMonth('tanggal_masuk', $bulan);
                        if ($tahun) $q->whereYear('tanggal_masuk', $tahun);
                    }
                })
                ->get()
                ->map(fn($d) => (object) [
                    'tanggal'     => $d->barangMasuk->tanggal_masuk ?? null,
                    'nama_barang' => $d->barang->nama_barang ?? $d->nama_barang,
                    'kode_barang' => $d->barang->kode_barang ?? '-',
                    'tipe'        => 'masuk',
                    'tipe_label'  => 'Masuk',
                    'qty'         => $d->qty_satuan,
                    'satuan'      => $d->satuan,
                    'subtotal'    => $d->subtotal,
                    'keterangan'  => 'No. Invoice: ' . ($d->barangMasuk->no_invoice ?? '-'),
                    'sumber_id'   => $d->id,
                ]);
        }

        $dataKeluarPerItem = collect();
        if (($tipe === 'semua' || $tipe === 'keluar') && ($subTipeKeluar === 'semua' || $subTipeKeluar === 'per_item')) {
            $dataKeluarPerItem = Transaksi_keluar_detail::with(['barang', 'transaksiKeluar.bus'])
                ->where('tipe', 'per_item')
                ->whereHas('barang', function ($q) use ($namaBarang) {
                    if ($namaBarang) $q->where('nama_barang', 'like', '%' . $namaBarang . '%');
                })
                ->whereHas('transaksiKeluar', function ($q) use ($tanggal, $bulan, $tahun) {
                    if ($tanggal) {
                        $q->whereDate('tanggal', $tanggal);
                    } else {
                        if ($bulan) $q->whereMonth('tanggal', $bulan);
                        if ($tahun) $q->whereYear('tanggal', $tahun);
                    }
                })
                ->get()
                ->map(fn($d) => (object) [
                    'tanggal'     => $d->transaksiKeluar->tanggal ?? null,
                    'nama_barang' => $d->barang->nama_barang ?? $d->nama_item,
                    'kode_barang' => $d->barang->kode_barang ?? '-',
                    'tipe'        => 'keluar_per_item',
                    'tipe_label'  => 'Keluar (Per Item)',
                    'qty'         => $d->qty,
                    'satuan'      => $d->satuan,
                    'subtotal'    => $d->subtotal,
                    'keterangan'  => 'Bus: ' . ($d->transaksiKeluar->bus->nama_bus ?? '-'),
                    'sumber_id'   => $d->id,
                ]);
        }

        $dataKeluarPaket = collect();
        if (($tipe === 'semua' || $tipe === 'keluar') && ($subTipeKeluar === 'semua' || $subTipeKeluar === 'paket_service')) {
            $dataKeluarPaket = Transaksi_keluar_detail::with(['paketService.paketServiceItem.barang', 'transaksiKeluar.bus'])
                ->where('tipe', 'paket_service')
                ->whereHas('transaksiKeluar', function ($q) use ($tanggal, $bulan, $tahun) {
                    if ($tanggal) {
                        $q->whereDate('tanggal', $tanggal);
                    } else {
                        if ($bulan) $q->whereMonth('tanggal', $bulan);
                        if ($tahun) $q->whereYear('tanggal', $tahun);
                    }
                })
                ->get()
                ->flatMap(function ($d) use ($namaBarang) {
                    if (!$d->paketService) return collect();

                    return $d->paketService->paketServiceItem
                        ->filter(function ($item) use ($namaBarang) {
                            if (!$namaBarang) return true;
                            return $item->barang && stripos($item->barang->nama_barang, $namaBarang) !== false;
                        })
                        ->map(fn($item) => (object) [
                            'tanggal'     => $d->transaksiKeluar->tanggal ?? null,
                            'nama_barang' => $item->barang->nama_barang ?? '-',
                            'kode_barang' => $item->barang->kode_barang ?? '-',
                            'tipe'        => 'keluar_paket_service',
                            'tipe_label'  => 'Keluar (Paket Service)',
                            'qty'         => $item->qty,
                            'satuan'      => $item->barang->satuan ?? '-',
                            'subtotal'    => null,
                            'keterangan'  => 'Paket: ' . ($d->paketService->nama_paket ?? '-')
                                            . ' | Bus: ' . ($d->transaksiKeluar->bus->nama_bus ?? '-'),
                            'sumber_id'   => $d->id . '-' . $item->id,
                        ]);
                });
        }

        return $dataMasuk
            ->concat($dataKeluarPerItem)
            ->concat($dataKeluarPaket)
            ->sortByDesc('tanggal')
            ->values();
    }

    private function buatLabelPeriode($tanggal, $bulan, $tahun): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($tanggal) {
            return \Carbon\Carbon::parse($tanggal)->format('d-m-Y');
        }
        if ($bulan && $tahun) {
            return ($namaBulan[$bulan] ?? $bulan) . ' ' . $tahun;
        }
        if ($tahun) {
            return 'Tahun ' . $tahun;
        }
        return 'Semua Periode';
    }
}