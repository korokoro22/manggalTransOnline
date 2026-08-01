<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Barang_masuk;
use App\Models\Barang_masuk_detail;
use App\Models\Bus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Cloudinary\Cloudinary;

class BarangMasukController extends Controller {
    public function index(Request $request)
    {
        $query = Barang_masuk::with('details')->orderBy('created_at', 'desc');

        if ($request->filled('no_invoice')) {
            $query->where('no_invoice', 'like', '%' . $request->no_invoice . '%');
        }

        if ($request->filled('nama_barang')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
            });
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
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

        if ($request->filled('kategori_nota')) {
            $query->where('kategori_nota', $request->kategori_nota);
        }

        $barangMasuks = $query->get();

        return view('barang-masuk.index', compact('barangMasuks'));
    }

    public function create()
    {
        $busList = Bus::orderBy('nama_bus')->get();
        
        $barangs = Barang::where('stok_saat_ini', '>', 0)
                     ->orderBy('nama_barang')
                     ->get()
                     ->map(fn($b) => [
                         'id'            => $b->id,
                         'kode_barang'   => $b->kode_barang,
                         'nama_barang'   => $b->nama_barang,
                         'foto'          => $b->foto,
                         'harga_jual'    => $b->harga_jual,
                         'satuan'        => $b->satuan,
                         'stok_saat_ini' => $b->stok_saat_ini,
                         'gudang'        => $b->gudang,
                         'tanggal_masuk' => $b->tanggal_masuk
                             ? \Carbon\Carbon::parse($b->tanggal_masuk)->format('d-m-Y')
                             : '-',
                     ])
                     ->keyBy('id');

        $kategoriList = Barang::select('kategori')
                        ->whereNotNull('kategori')
                        ->distinct()
                        ->pluck('kategori')
                        ->reject(fn($k) => $k === 'item_bebas')
                        ->values();

        return view('barang-masuk.create', compact('busList', 'barangs', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_masuk'         => 'required|date',
            'no_invoice'            => 'required|string',
            'supplier'              => 'required|string',
            'penerima'              => 'required|string',
            'bukti_nota'            => 'nullable|image|mimes:jpg,jpeg,png|max:6144',
            'kategori_nota'         => 'required|in:nota_bengkel,nota_jalan',

            'items'                 => 'required|array|min:1',
            'items.*.jenis'         => 'required|in:barang_baru,barang_ada',
            'items.*.nama_barang'   => 'required_if:items.*.jenis,barang_baru|nullable|string',
            'items.*.barang_id'     => 'required_if:items.*.jenis,barang_ada|nullable|string',
            'items.*.kategori'      => 'required|string', 
            'items.*.kode_barang'   => 'string',
            'items.*.foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:6144',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.satuan'        => 'required|string',
            'items.*.qty_satuan'    => 'required|integer|min:1',
            'items.*.harga_jual'    => 'required|numeric|min:0',
            'items.*.subtotal'      => 'required|numeric|min:0',
            'items.*.gudang'        => 'required|in:gudang_utama,gudang_2,gudang_3',
        ]);

        DB::transaction(function () use ($request) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            $buktiNotaPath = null;
            if ($request->hasFile('bukti_nota')) {
                $upload = $cloudinary->uploadApi()->upload($request->file('bukti_nota')->getRealPath(), [
                    'folder' => 'bukti_nota'
                ]);
                $buktiNotaPath = $upload['secure_url'];
            }

            $barangMasuk = Barang_masuk::create([
                'no_invoice'    => $request->no_invoice,
                'supplier'      => $request->supplier,
                'kategori_nota' => $request->kategori_nota,
                'penerima'      => $request->penerima,
                'tanggal_masuk' => $request->tanggal_masuk,
                'bukti_nota'    => $buktiNotaPath,
            ]);

            foreach ($request->items as $index => $item) {
                $barangReferensi = null;
                if ($item['jenis'] === 'barang_ada') {
                    $barangReferensi = Barang::findOrFail($item['barang_id']);
                }

                $namaBarang = $item['jenis'] === 'barang_ada'
                    ? $barangReferensi->nama_barang
                    : $item['nama_barang'];

                $fotoPath = null;
                $fotoFile = $request->file("items.{$index}.foto");
                if ($fotoFile && $fotoFile->isValid()) {
                    $upload = $cloudinary->uploadApi()->upload($fotoFile->getRealPath(), [
                        'folder' => 'barang'
                    ]);
                    $fotoPath = $upload['secure_url'];
                }

                if (!$fotoPath && $item['jenis'] === 'barang_ada' && $barangReferensi && $barangReferensi->foto) {
                    $fotoPath = $barangReferensi->foto;
                }

                $kategoriInput = Str::slug($item['kategori'], '_');

                $barang = Barang::create([
                    'kode_barang'   => $item['kode_barang'],
                    'nama_barang'   => $namaBarang,
                    'kategori'      => $kategoriInput,
                    'gudang'        => $item['gudang'],
                    'foto'          => $fotoPath,
                    'qty'           => $item['qty'],
                    'satuan'        => $item['satuan'],
                    'qty_satuan'    => $item['qty_satuan'],
                    'stok_saat_ini' => $item['qty_satuan'],
                    'harga_jual'    => $item['harga_jual'],
                    'tanggal_masuk' => $request->tanggal_masuk,
                    'qr_code'       => null,
                ]);

                $qrData = (string) $barang->id;
                $qrFolder = storage_path('app/public/qrcode');
                if (!file_exists($qrFolder)) {
                    mkdir($qrFolder, 0755, true);
                }

                $namaQr     = 'barang-' . Str::slug($barang->nama_barang) . '-' . $barang->id . '.svg';
                $qrFileName = 'qrcode/' . $namaQr;
                $qrFilePath = storage_path('app/public/' . $qrFileName);

                QrCode::format('svg')
                    ->size(200)
                    ->errorCorrection('H')
                    ->generate($qrData, $qrFilePath);

                $barang->update(['qr_code' => $qrFileName]);

                Barang_masuk_detail::create([
                    'barang_masuk_id' => $barangMasuk->id,
                    'barang_id'       => $barang->id,
                    'nama_barang'     => $namaBarang,
                    'foto'            => $fotoPath,
                    'qty'             => $item['qty'],
                    'satuan'          => $item['satuan'],
                    'qty_satuan'      => $item['qty_satuan'],
                    'harga_jual'      => $item['harga_jual'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }
        });

        return redirect()->route('barang-masuk.index')
            ->with('success', 'Data barang masuk berhasil disimpan.');
    }

    public function show($id)
    {
        $barangMasuk = Barang_masuk::with([
            'details.barang.transaksiKeluarDetails.transaksiKeluar'
        ])->findOrFail($id);

        return view('barang-masuk.show', compact('barangMasuk'));
    }

    public function edit($id)
    {
        $barangMasuk = Barang_masuk::with('details.barang')->findOrFail($id);

        $existingBarangs = Barang::orderByDesc('tanggal_masuk')
            ->get()
            ->groupBy('nama_barang')
            ->map(function ($group) {
                $latest = $group->first();
                return [
                    'nama_barang' => $latest->nama_barang,
                    'foto'        => $latest->foto,
                    'kategori'    => $latest->kategori,
                ];
            })
            ->values();

        $barangs = Barang::where('stok_saat_ini', '>', 0)
            ->orderBy('nama_barang')
            ->get()
            ->map(fn($b) => [
                'id'            => $b->id,
                'kode_barang'   => $b->kode_barang,
                'nama_barang'   => $b->nama_barang,
                'foto'          => $b->foto,
                'harga_jual'    => $b->harga_jual,
                'satuan'        => $b->satuan,
                'stok_saat_ini' => $b->stok_saat_ini,
                'gudang'        => $b->gudang,
                'tanggal_masuk' => $b->tanggal_masuk ? \Carbon\Carbon::parse($b->tanggal_masuk)->format('d-m-Y') : '-',
            ])
            ->keyBy('id');

        $kategoriList = \App\Models\Barang::select('kategori')
                        ->whereNotNull('kategori')
                        ->distinct()
                        ->pluck('kategori')
                        ->reject(fn($k) => $k === 'item_bebas')
                        ->values();

        return view('barang-masuk.edit', compact('barangMasuk', 'existingBarangs', 'barangs', 'kategoriList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_masuk'         => 'required|date',
            'no_invoice'            => 'required|string',
            'supplier'              => 'required|string',
            'penerima'              => 'required|string',
            'bukti_nota'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'items'                 => 'required|array|min:1',
            'items.*.jenis'         => 'nullable|in:barang_baru,barang_ada',
            'items.*.nama_barang'   => 'required_if:items.*.jenis,barang_baru|nullable|string',
            'items.*.barang_id'     => 'required_if:items.*.jenis,barang_ada|nullable|string',
            'items.*.kode_barang'   => 'string',
            'items.*.kategori'      => 'required|string',
            'items.*.gudang'        => 'required|in:gudang_utama,gudang_2,gudang_3',
            'items.*.foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.satuan'        => 'required|string',
            'items.*.qty_satuan'    => 'required|integer|min:1',
            'items.*.harga_jual'    => 'required|numeric|min:0',
            'items.*.subtotal'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $barangMasuk = Barang_masuk::with('details.barang')->findOrFail($id);

            $buktiNotaPath = $barangMasuk->bukti_nota;
            if ($request->hasFile('bukti_nota')) {
                if ($buktiNotaPath) {
                    preg_match('/upload\/(?:v\d+\/)?([^\.]+)/', $buktiNotaPath, $matches);
                    if (!empty($matches[1])) {
                        $cloudinary->uploadApi()->destroy($matches[1]);
                    }
                }
                $upload = $cloudinary->uploadApi()->upload($request->file('bukti_nota')->getRealPath(), [
                    'folder' => 'bukti_nota'
                ]);
                $buktiNotaPath = $upload['secure_url'];
            }

            $barangMasuk->update([
                'no_invoice'    => $request->no_invoice,
                'supplier'      => $request->supplier,
                'penerima'      => $request->penerima,
                'tanggal_masuk' => $request->tanggal_masuk,
                'bukti_nota'    => $buktiNotaPath,
            ]);

            $fotoLama = [];
            foreach ($barangMasuk->details as $index => $detail) {
                $fotoLama[$index] = $detail->foto;
            }

            foreach ($barangMasuk->details as $detail) {
                if ($detail->barang) {
                    if ($detail->barang->qr_code) {
                        Storage::disk('public')->delete($detail->barang->qr_code);
                    }
                    $detail->barang->delete();
                }
                $detail->delete();
            }

            foreach ($request->items as $index => $item) {
                $jenis = $item['jenis'] ?? 'bawaan';
                
                $barangReferensi = null;
                if ($jenis === 'barang_ada') {
                    $barangReferensi = Barang::findOrFail($item['barang_id']);
                    $namaBarang = $barangReferensi->nama_barang;
                } else {
                    $namaBarang = $item['nama_barang'];
                }

                $fotoFile = $request->file("items.{$index}.foto");
                $fotoPath = null;

                if ($fotoFile && $fotoFile->isValid()) {
                    if (isset($fotoLama[$index]) && $fotoLama[$index]) {
                        preg_match('/upload\/(?:v\d+\/)?([^\.]+)/', $fotoLama[$index], $matches);
                        if (!empty($matches[1])) {
                            $cloudinary->uploadApi()->destroy($matches[1]);
                        }
                    }
                    $upload = $cloudinary->uploadApi()->upload($fotoFile->getRealPath(), [
                        'folder' => 'barang'
                    ]);
                    $fotoPath = $upload['secure_url'];
                } else {
                    if ($jenis === 'barang_ada' && $barangReferensi && $barangReferensi->foto) {
                        $fotoPath = $barangReferensi->foto;
                    } else {
                        $fotoPath = $fotoLama[$index] ?? null;
                    }
                }

                $barang = Barang::create([
                    'kode_barang'   => $item['kode_barang'],
                    'nama_barang'   => $namaBarang,
                    'kategori'      => $item['kategori'],
                    'gudang'        => $item['gudang'],
                    'foto'          => $fotoPath,
                    'qty'           => $item['qty'],
                    'satuan'        => $item['satuan'],
                    'qty_satuan'    => $item['qty_satuan'],
                    'stok_saat_ini' => $item['qty_satuan'],
                    'harga_jual'    => $item['harga_jual'],
                    'tanggal_masuk' => $request->tanggal_masuk,
                    'qr_code'       => null,
                ]);

                $qrData = (string) $barang->id;
                $qrFolder = storage_path('app/public/qrcode');
                if (!file_exists($qrFolder)) {
                    mkdir($qrFolder, 0755, true);
                }

                $namaQr     = 'barang-' . Str::slug($barang->nama_barang) . '-' . $barang->id . '.svg';
                $qrFileName = 'qrcode/' . $namaQr;
                $qrFilePath = storage_path('app/public/' . $qrFileName);

                QrCode::format('svg')
                    ->size(200)
                    ->errorCorrection('H')
                    ->generate($qrData, $qrFilePath);

                $barang->update(['qr_code' => $qrFileName]);

                Barang_masuk_detail::create([
                    'barang_masuk_id' => $barangMasuk->id,
                    'barang_id'       => $barang->id,
                    'nama_barang'     => $namaBarang,
                    'foto'            => $fotoPath,
                    'qty'             => $item['qty'],
                    'satuan'          => $item['satuan'],
                    'qty_satuan'      => $item['qty_satuan'],
                    'harga_jual'      => $item['harga_jual'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }
        });

        return redirect()->route('barang-masuk.index')
                        ->with('success', 'Data barang masuk berhasil diupdate.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $barangMasuk = Barang_masuk::with('details.barang')->findOrFail($id);
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            foreach ($barangMasuk->details as $detail) {
                if ($detail->foto) {
                    preg_match('/upload\/(?:v\d+\/)?([^\.]+)/', $detail->foto, $matches);
                    if (!empty($matches[1])) {
                        $cloudinary->uploadApi()->destroy($matches[1]);
                    }
                }

                if ($detail->barang) {
                    if ($detail->barang->qr_code) {
                        Storage::disk('public')->delete($detail->barang->qr_code);
                    }
                    $detail->barang->delete();
                }
                $detail->delete();
            }

            if ($barangMasuk->bukti_nota) {
                preg_match('/upload\/(?:v\d+\/)?([^\.]+)/', $barangMasuk->bukti_nota, $matches);
                if (!empty($matches[1])) {
                    $cloudinary->uploadApi()->destroy($matches[1]);
                }
            }

            $barangMasuk->delete();
        });

        return redirect()->route('barang-masuk.index')
                        ->with('success', 'Data barang masuk beserta gambar berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $query = Barang_masuk::with('details')->orderBy('created_at', 'desc');

        if ($request->filled('no_invoice')) {
            $query->where('no_invoice', 'like', '%' . $request->no_invoice . '%');
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_masuk', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_masuk', $request->tahun);
        }

        $barangMasuks = $query->get();

        $pdf = Pdf::loadView('barang-masuk.pdf-index', compact('barangMasuks'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('barang-masuk-' . now()->format('d-m-Y') . '.pdf');
    }

    public function exportPdfShow($id)
    {
        $barangMasuk = Barang_masuk::with('details')->findOrFail($id);
        $storagePath = storage_path('app/public/');

        $pdf = Pdf::loadView('barang-masuk.pdf-show', compact('barangMasuk', 'storagePath'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('nota-' . $barangMasuk->no_invoice . '.pdf');
    }
}