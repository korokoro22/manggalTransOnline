<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MasterBarangController extends Controller {
    // public function index(Request $request)
    // {
    //     $query = Barang::orderBy('created_at', 'desc');

    //     if ($request->filled('nama_barang')) {
    //         $query->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
    //     }

    //      if ($request->filled('tanggal')) {
    //         $query->whereDate('tanggal_masuk', $request->tanggal);
    //     } else {
    //         if ($request->filled('bulan')) {
    //             $query->whereMonth('tanggal_masuk', $request->bulan);
    //         }
    //         if ($request->filled('tahun')) {
    //             $query->whereYear('tanggal_masuk', $request->tahun);
    //         }
    //     }

    //     $barangs = $query->paginate(10)->withQueryString();

    //     return view('master-barang.index', compact('barangs'));
    // }

    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('nama_barang')) {
            $query->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
        }

        if ($request->filled('gudang')) {
            $query->where('gudang', $request->gudang);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_masuk', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_masuk', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_masuk', $request->tahun);
            }
        }

        $barangs = $query
            ->selectRaw('
                nama_barang,
                COUNT(*) as jumlah_batch,
                SUM(stok_saat_ini) as total_stok,
                MAX(tanggal_masuk) as tanggal_masuk_terakhir,
                MIN(harga_jual) as harga_terendah,
                MAX(harga_jual) as harga_tertinggi
            ')
            ->groupBy('nama_barang')
            ->orderByDesc('tanggal_masuk_terakhir')
            ->paginate(10)
            ->withQueryString();

        // Ambil 1 sample barang (terbaru) per nama_barang untuk foto, kode_barang, gudang, qr_code
        $namaList = $barangs->pluck('nama_barang');

        $samples = Barang::whereIn('nama_barang', $namaList)
            ->orderByDesc('tanggal_masuk')
            ->get()
            ->groupBy('nama_barang')
            ->map(fn($group) => $group->first()); // ambil batch terbaru sebagai representasi

        return view('master-barang.index', compact('barangs', 'samples'));
    }

    public function showByNama(Request $request, $nama_barang)
    {
        $query = Barang::where('nama_barang', $nama_barang);

        if ($request->filled('gudang')) {
            $query->where('gudang', $request->gudang);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_masuk', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_masuk', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_masuk', $request->tahun);
            }
        }

        $barangs = $query->orderByDesc('tanggal_masuk')->get();

        return view('master-barang.show-by-nama', compact('barangs', 'nama_barang'));
    }

    public function show($id)
    {
        $barang = Barang::with([
            'transaksiKeluarDetail.transaksiKeluar.bus',
            'barangMasukDetail.barangMasuk'
        ])->findOrFail($id);

        return view('master-barang.show', compact('barang'));
    }

    // Controller
    public function exportPdfByNama(Request $request, $nama_barang)
    {
        $query = Barang::where('nama_barang', $nama_barang);

        if ($request->filled('gudang')) {
            $query->where('gudang', $request->gudang);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_masuk', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_masuk', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_masuk', $request->tahun);
            }
        }

        $barangs = $query->orderByDesc('tanggal_masuk')->get();

        $pdf = Pdf::loadView('master-barang.pdf-show-by-nama', compact('barangs', 'nama_barang'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('batch-' . Str::slug($nama_barang) . '-' . now()->format('d-m-Y') . '.pdf');
    }

    // public function exportPdf(Request $request)
    // {
    //     $query = Barang::orderBy('created_at', 'desc');

    //     if ($request->filled('nama_barang')) {
    //         $query->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
    //     }

    //     if ($request->filled('bulan')) {
    //         $query->whereMonth('tanggal_masuk', $request->bulan);
    //     }

    //     if ($request->filled('tahun')) {
    //         $query->whereYear('tanggal_masuk', $request->tahun);
    //     }

    //     $barangs = $query->get();
    //     $storagePath = storage_path('app/public/');

    //     $pdf = Pdf::loadView('master-barang.pdf-index', compact('barangs', 'storagePath'))
    //             ->setPaper('a4', 'landscape');

    //     return $pdf->download('master-barang-' . now()->format('d-m-Y') . '.pdf');
    // }

    public function exportPdf(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('nama_barang')) {
            $query->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
        }

        if ($request->filled('gudang')) {
            $query->where('gudang', $request->gudang);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_masuk', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_masuk', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_masuk', $request->tahun);
            }
        }

        $barangs = $query
            ->selectRaw('
                nama_barang,
                COUNT(*) as jumlah_batch,
                SUM(stok_saat_ini) as total_stok,
                MAX(tanggal_masuk) as tanggal_masuk_terakhir,
                MIN(harga_jual) as harga_terendah,
                MAX(harga_jual) as harga_tertinggi
            ')
            ->groupBy('nama_barang')
            ->orderByDesc('tanggal_masuk_terakhir')
            ->get();

        $pdf = Pdf::loadView('master-barang.pdf-index', compact('barangs'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('master-barang-' . now()->format('d-m-Y') . '.pdf');
    }

    public function exportPdfShow($id)
    {
        $barang = Barang::with('transaksiKeluarDetail.transaksiKeluar.bus')->findOrFail($id);
        $storagePath = storage_path('app/public/');

        $pdf = Pdf::loadView('master-barang.pdf-show', compact('barang', 'storagePath'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('barang-' . $barang->kode_barang . '.pdf');
    }

    public function getJson($id)
    {
        $barang = Barang::findOrFail($id);

        return response()->json([
            'id'          => $barang->id,
            'kode_barang' => $barang->kode_barang,
            'nama_barang' => $barang->nama_barang,
            'kategori'    => $barang->kategori,
            'harga_jual'  => $barang->harga_jual,
            'satuan'      => $barang->satuan,
            'stok_saat_ini' => $barang->stok_saat_ini,
            'foto'        => $barang->foto ? asset('storage/' . $barang->foto) : null,
        ]);
    }
}