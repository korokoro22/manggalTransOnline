<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Bus;
use App\Models\Paket_service;
use App\Models\Paket_service_item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaketServiceController extends Controller {
    public function index(Request $request)
    {
        $query = Paket_service::with('bus', 'paketServiceItem.barang')
                             ->orderBy('created_at', 'desc');

        if ($request->filled('nama_paket')) {
            $query->where('nama_paket', 'like', '%' . $request->nama_paket . '%');
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        $paketServices = $query->get();
        $buses         = Bus::orderBy('nama_bus')->get();

        return view('paket-service.index', compact('paketServices', 'buses'));
    }

    public function show($id)
    {
        $paketService = Paket_service::with('bus', 'paketServiceItem.barang')->findOrFail($id);
        return view('paket-service.show', compact('paketService'));
    }

    // public function create()
    // {
    //     $buses   = Bus::orderBy('nama_bus')->get();
    //     $barangs = Barang::whereIn('kategori', ['oli_mesin', 'filter_solar'])
    //                      ->where('stok_saat_ini', '>', 0)
    //                      ->orderBy('nama_barang')
    //                      ->get();

    //     return view('paket-service.create', compact('buses', 'barangs'));
    // }

    public function create()
    {
        $buses   = Bus::orderBy('nama_bus')->get();
        
        // Mengambil semua barang dari database tanpa batasan kategori tertentu
        $barangs = Barang::orderBy('nama_barang')
                    ->get()
                    ->map(fn($b) => [
                        'id'             => $b->id,
                        'kode_barang'    => $b->kode_barang,
                        'nama_barang'    => $b->nama_barang,
                        'foto'           => $b->foto ? asset('storage/' . $b->foto) : null,
                        'harga_jual'     => $b->harga_jual,
                        'satuan'         => $b->satuan,
                        'stok_saat_ini'  => $b->stok_saat_ini,
                        'gudang'         => $b->gudang,
                        // Label kategori otomatis rapi (contoh: 'oli_mesin' -> 'Oli Mesin', 'kampas_rem' -> 'Kampas Rem')
                        'kategori_label' => ucwords(str_replace('_', ' ', $b->kategori))
                    ])
                    ->keyBy('id');

        return view('paket-service.create', compact('buses', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bus_id'              => 'required|exists:bus,id',
            'nama_paket'          => 'required|string',
            'harga'               => 'required|numeric|min:0',
            'items'               => 'required|array|min:1',
            'items.*.barang_id'   => 'required|exists:barang,id',
            'items.*.qty'         => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            // Simpan paket service
            $paketService = Paket_service::create([
                'bus_id'     => $request->bus_id,
                'nama_paket' => $request->nama_paket,
                'harga'      => $request->harga,
            ]);

            // Simpan item paket service
            foreach ($request->items as $item) {
                Paket_service_item::create([
                    'paket_service_id' => $paketService->id,
                    'barang_id'        => $item['barang_id'],
                    'qty'              => $item['qty'],
                ]);
            }
        });

        return redirect()->route('paket-service.index')
                         ->with('success', 'Paket service berhasil ditambahkan.');
    }

    public function edit($id)
{
    $paketService = Paket_service::with('paketServiceItem.barang')->findOrFail($id);
    $buses        = Bus::orderBy('nama_bus')->get();
    
    $barangs = Barang::whereIn('kategori', ['oli_mesin', 'filter_solar'])
                    ->orderBy('nama_barang')
                    ->get()
                    ->map(fn($b) => [
                        'id'             => $b->id,
                        'kode_barang'    => $b->kode_barang,
                        'nama_barang'    => $b->nama_barang,
                        'foto'           => $b->foto ? asset('storage/' . $b->foto) : null,
                        'harga_jual'     => $b->harga_jual,
                        'satuan'         => $b->satuan,
                        'stok_saat_ini'  => $b->stok_saat_ini,
                        'gudang'         => $b->gudang, // <-- Tambahan field gudang
                        'kategori_label' => $b->kategori == 'oli_mesin' ? 'Oli Mesin' : 'Filter Solar'
                    ])
                    ->keyBy('id');

    return view('paket-service.edit', compact('paketService', 'buses', 'barangs'));
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'bus_id'              => 'required|exists:bus,id',
            'nama_paket'          => 'required|string',
            'harga'               => 'required|numeric|min:0',
            'items'               => 'required|array|min:1',
            'items.*.barang_id'   => 'required|exists:barang,id',
            'items.*.qty'         => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $id) {

            $paketService = Paket_service::findOrFail($id);

            // Update paket service
            $paketService->update([
                'bus_id'     => $request->bus_id,
                'nama_paket' => $request->nama_paket,
                'harga'      => $request->harga,
            ]);

            // Hapus item lama lalu insert baru
            $paketService->paketServiceItem()->delete();

            foreach ($request->items as $item) {
                Paket_service_item::create([
                    'paket_service_id' => $paketService->id,
                    'barang_id'        => $item['barang_id'],
                    'qty'              => $item['qty'],
                ]);
            }
        });

        return redirect()->route('paket-service.index')
                         ->with('success', 'Paket service berhasil diupdate.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $paketService = Paket_service::findOrFail($id);

            // Hapus semua item paket dulu
            $paketService->paketServiceItem()->delete();

            // Hapus paket service
            $paketService->delete();
        });

        return redirect()->route('paket-service.index')
                         ->with('success', 'Paket service berhasil dihapus.');
    }

    public function exportPdfShow($id)
    {
        $paketService = Paket_service::with('bus', 'paketServiceItem.barang')->findOrFail($id);

        $pdf = Pdf::loadView('paket-service.pdf-show', compact('paketService'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('paket-service-' . Str::slug($paketService->nama_paket) . '.pdf');
    }

    public function exportPdf(Request $request)
    {
        $query = Paket_service::with('bus', 'paketServiceItem.barang')
                            ->orderBy('created_at', 'desc');

        if ($request->filled('nama_paket')) {
            $query->where('nama_paket', 'like', '%' . $request->nama_paket . '%');
        }

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        $paketServices = $query->get();

        $pdf = Pdf::loadView('paket-service.pdf-index', compact('paketServices'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('paket-service-' . now()->format('d-m-Y') . '.pdf');
    }
}