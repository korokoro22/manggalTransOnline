<?php

namespace App\Http\Controllers;

use App\Models\Transaksi_keluar;
use App\Models\Transaksi_keluar_detail;
use App\Models\Bus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotaJalanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi_keluar::with('bus', 'details')->notaJalan();

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal', $request->tahun);
            }
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('nama_item')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('nama_item', 'like', '%' . $request->nama_item . '%');
            });
        }

        $notaJalans = $query->latest('tanggal')->paginate(10)->withQueryString();

        $buses = Bus::all();

        $tahunList = Transaksi_keluar::notaJalan()
            ->selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('nota-jalan.index', compact('notaJalans', 'buses', 'tahunList'));
    }

    // public function create()
    // {
    //     $buses = Bus::all();
    //     return view('nota-jalan.create', compact('buses'));
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'bus_id'                 => 'required|exists:bus,id',
    //         'tanggal'                => 'required|date',
    //         'no_invoice'             => 'required|string|max:255',
    //         'supplier'               => 'required|string|max:255',
    //         'bukti_nota'             => 'nullable|image|max:2048',
    //         'total'                  => 'required|integer|min:0',

    //         'items'                  => 'required|array|min:1',
    //         'items.*.tipe'           => 'required|in:per_item,biaya_pengerjaan',
    //         'items.*.nama_item'      => 'nullable|string|max:255',
    //         'items.*.qty'            => 'nullable|integer|min:1',
    //         'items.*.satuan'         => 'nullable|string|max:50',
    //         'items.*.harga_satuan'   => 'nullable|integer|min:0',
    //         'items.*.keterangan'     => 'nullable|string|max:255',
    //         'items.*.subtotal'       => 'required|integer|min:0',
    //     ]);

    //     // Validasi manual per item, karena required_if wildcard kurang reliable
    //     foreach ($validated['items'] as $index => $item) {
    //         if ($item['tipe'] === 'per_item') {
    //             if (empty($item['nama_item']) || empty($item['qty']) || empty($item['satuan']) || !isset($item['harga_satuan'])) {
    //                 return redirect()->back()
    //                     ->withInput()
    //                     ->with('error', "Item #" . ($index + 1) . ": Nama item, qty, satuan, dan harga satuan wajib diisi untuk tipe Per Item.");
    //             }
    //         } else { // biaya_pengerjaan
    //             if (empty($item['keterangan'])) {
    //                 return redirect()->back()
    //                     ->withInput()
    //                     ->with('error', "Item #" . ($index + 1) . ": Keterangan pengerjaan wajib diisi untuk tipe Biaya Pengerjaan.");
    //             }
    //         }
    //     }

    //     $buktiNotaPath = null;
    //     if ($request->hasFile('bukti_nota')) {
    //         $buktiNotaPath = $request->file('bukti_nota')->store('nota-jalan', 'public');
    //     }

    //     $transaksi = Transaksi_keluar::create([
    //         'bus_id'          => $validated['bus_id'],
    //         'kategori'        => 'nota_jalan',
    //         'tanggal'         => $validated['tanggal'],
    //         'no_invoice'      => $validated['no_invoice'],
    //         'supplier'        => $validated['supplier'],
    //         'bukti_nota'      => $buktiNotaPath,
    //         'total_transaksi' => $validated['total'],
    //     ]);

    //     foreach ($validated['items'] as $item) {
    //         if ($item['tipe'] === 'biaya_pengerjaan') {
    //             Transaksi_keluar_detail::create([
    //                 'transaksi_keluar_id' => $transaksi->id,
    //                 'tipe'                => 'biaya_pengerjaan',
    //                 'nama_item'           => $item['keterangan'],
    //                 'keterangan'          => $item['keterangan'],
    //                 'qty'                 => null,
    //                 'satuan'              => null,
    //                 'harga_satuan'        => null,
    //                 'subtotal'            => $item['subtotal'],
    //             ]);
    //         } else {
    //             Transaksi_keluar_detail::create([
    //                 'transaksi_keluar_id' => $transaksi->id,
    //                 'tipe'                => 'per_item',
    //                 'nama_item'           => $item['nama_item'],
    //                 'qty'                 => $item['qty'],
    //                 'satuan'              => $item['satuan'],
    //                 'harga_satuan'        => $item['harga_satuan'],
    //                 'subtotal'            => $item['subtotal'],
    //             ]);
    //         }
    //     }

    //     return redirect()->route('nota-jalan.index')
    //         ->with('success', 'Nota jalan berhasil disimpan.');
    // }

    public function create()
    {
        $buses = Bus::all();
        return view('nota-jalan.create', compact('buses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_id'                 => 'required|exists:bus,id',
            'tanggal'                => 'required|date',
            'no_invoice'             => 'required|string|max:255',
            'supplier'               => 'required|string|max:255',
            'bukti_nota'             => 'nullable|image|max:2048',
            'total'                  => 'required|integer|min:0',

            'items'                  => 'required|array|min:1',
            'items.*.tipe'           => 'required|in:per_item,biaya_pengerjaan',
            'items.*.nama_item'      => 'nullable|string|max:255',
            'items.*.qty'            => 'nullable|integer|min:1',
            'items.*.satuan'         => 'nullable|string|max:50',
            'items.*.harga_satuan'   => 'nullable|integer|min:0',
            'items.*.keterangan'     => 'nullable|string|max:255',
            'items.*.subtotal'       => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $index => $item) {
            if ($item['tipe'] === 'per_item') {
                if (empty($item['nama_item']) || empty($item['qty']) || empty($item['satuan']) || !isset($item['harga_satuan'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Item #" . ($index + 1) . ": Nama item, qty, satuan, dan harga satuan wajib diisi untuk tipe Per Item.");
                }
            } else {
                if (empty($item['keterangan'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Item #" . ($index + 1) . ": Keterangan pengerjaan wajib diisi untuk tipe Biaya Pengerjaan.");
                }
            }
        }

        $buktiNotaPath = null;
        if ($request->hasFile('bukti_nota')) {
            $buktiNotaPath = $request->file('bukti_nota')->store('nota-jalan', 'public');
        }

        $transaksi = Transaksi_keluar::create([
            'bus_id'          => $validated['bus_id'],
            'kategori'        => 'nota_jalan',
            'tanggal'         => $validated['tanggal'],
            'no_invoice'      => $validated['no_invoice'],
            'supplier'        => $validated['supplier'],
            'bukti_nota'      => $buktiNotaPath,
            'total_transaksi' => $validated['total'],
        ]);

        foreach ($validated['items'] as $item) {
            if ($item['tipe'] === 'biaya_pengerjaan') {
                Transaksi_keluar_detail::create([
                    'transaksi_keluar_id' => $transaksi->id,
                    'tipe'                => 'biaya_pengerjaan',
                    'nama_item'           => $item['keterangan'] ?? null,
                    'keterangan'          => $item['keterangan'] ?? null,
                    'qty'                 => null,
                    'satuan'              => null,
                    'harga_satuan'        => null,
                    'subtotal'            => $item['subtotal'] ?? 0,
                ]);
            } else {
                Transaksi_keluar_detail::create([
                    'transaksi_keluar_id' => $transaksi->id,
                    'tipe'                => 'per_item',
                    'nama_item'           => $item['nama_item'] ?? null,
                    'qty'                 => $item['qty'] ?? null,
                    'satuan'              => $item['satuan'] ?? null,
                    'harga_satuan'        => $item['harga_satuan'] ?? null,
                    'subtotal'            => $item['subtotal'] ?? 0,
                ]);
            }
        }

        return redirect()->route('nota-jalan.index')
            ->with('success', 'Nota jalan berhasil disimpan.');
    }

    public function show($id)
    {
        $notaJalan = Transaksi_keluar::with('bus', 'details')->notaJalan()->findOrFail($id);

        return view('nota-jalan.show', compact('notaJalan'));
    }

    public function edit($id)
    {
        $notaJalan = Transaksi_keluar::with('details')->notaJalan()->findOrFail($id);
        $buses = Bus::all();

        return view('nota-jalan.edit', compact('notaJalan', 'buses'));
    }

    public function update(Request $request, $id)
    {
        $notaJalan = Transaksi_keluar::notaJalan()->findOrFail($id);

        $validated = $request->validate([
            'bus_id'                 => 'required|exists:bus,id',
            'tanggal'                => 'required|date',
            'no_invoice'             => 'required|string|max:255',
            'supplier'               => 'required|string|max:255',
            'bukti_nota'             => 'nullable|image|max:2048',
            // REVISI: Ubah integer menjadi numeric agar aman jika ada nilai .00 dari DB
            'total'                  => 'required|numeric|min:0', 

            'items'                  => 'required|array|min:1',
            'items.*.tipe'           => 'required|in:per_item,biaya_pengerjaan',
            'items.*.nama_item'      => 'nullable|string|max:255',
            'items.*.qty'            => 'nullable|numeric|min:1',
            'items.*.satuan'         => 'nullable|string|max:50',
            'items.*.harga_satuan'   => 'nullable|numeric|min:0',
            'items.*.keterangan'     => 'nullable|string|max:255',
            'items.*.subtotal'       => 'required|numeric|min:0',
        ]);

        foreach ($validated['items'] as $index => $item) {
            if ($item['tipe'] === 'per_item') {
                if (empty($item['nama_item']) || empty($item['qty']) || empty($item['satuan']) || !isset($item['harga_satuan'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Item #" . ($index + 1) . ": Nama item, qty, satuan, dan harga satuan wajib diisi untuk tipe Per Item.");
                }
            } else {
                if (empty($item['keterangan'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Item #" . ($index + 1) . ": Keterangan pengerjaan wajib diisi untuk tipe Biaya Pengerjaan.");
                }
            }
        }

        $buktiNotaPath = $notaJalan->bukti_nota;
        if ($request->hasFile('bukti_nota')) {
            if ($buktiNotaPath) {
                Storage::disk('public')->delete($buktiNotaPath);
            }
            $buktiNotaPath = $request->file('bukti_nota')->store('nota-jalan', 'public');
        }

        $notaJalan->update([
            'bus_id'          => $validated['bus_id'],
            'tanggal'         => $validated['tanggal'],
            'no_invoice'      => $validated['no_invoice'],
            'supplier'        => $validated['supplier'],
            'bukti_nota'      => $buktiNotaPath,
            'total_transaksi' => $validated['total'],
        ]);

        $notaJalan->details()->delete();

        foreach ($validated['items'] as $item) {
            if ($item['tipe'] === 'biaya_pengerjaan') {
                Transaksi_keluar_detail::create([
                    'transaksi_keluar_id' => $notaJalan->id,
                    'tipe'                => 'biaya_pengerjaan',
                    'nama_item'           => $item['keterangan'] ?? null,
                    'keterangan'          => $item['keterangan'] ?? null,
                    'qty'                 => null,
                    'satuan'              => null,
                    'harga_satuan'        => null,
                    'subtotal'            => $item['subtotal'] ?? 0,
                ]);
            } else {
                Transaksi_keluar_detail::create([
                    'transaksi_keluar_id' => $notaJalan->id,
                    'tipe'                => 'per_item',
                    'nama_item'           => $item['nama_item'] ?? null,
                    'qty'                 => $item['qty'] ?? null,
                    'satuan'              => $item['satuan'] ?? null,
                    'harga_satuan'        => $item['harga_satuan'] ?? null,
                    'subtotal'            => $item['subtotal'] ?? 0,
                ]);
            }
        }

        return redirect()->route('nota-jalan.index')
            ->with('success', 'Nota jalan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $notaJalan = Transaksi_keluar::notaJalan()->findOrFail($id);

        if ($notaJalan->bukti_nota) {
            Storage::disk('public')->delete($notaJalan->bukti_nota);
        }

        $notaJalan->details()->delete();
        $notaJalan->delete();

        return redirect()->route('nota-jalan.index')
            ->with('success', 'Nota jalan berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $query = Transaksi_keluar::with('bus', 'details')->notaJalan();

        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal', $request->tahun);
            }
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('nama_item')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('nama_item', 'like', '%' . $request->nama_item . '%');
            });
        }

        $notaJalans = $query->latest('tanggal')->get();

        $pdf = Pdf::loadView('nota-jalan.pdf-index', compact('notaJalans'))
                ->setPaper('a4', 'landscape');

        return $pdf->download('nota-jalan-' . now()->format('d-m-Y') . '.pdf');
    }

    public function exportPdfShow($id)
    {
        $notaJalan = Transaksi_keluar::with('bus', 'details')->notaJalan()->findOrFail($id);

        $pdf = Pdf::loadView('nota-jalan.pdf-show', compact('notaJalan'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('nota-jalan-' . $notaJalan->bus->plat_nomor . '-' . $notaJalan->no_invoice . '.pdf');
    }
}