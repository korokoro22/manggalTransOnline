@extends('adminlte::page')

@section('title', 'Detail Barang')

@section('content_header')
    <h1>DETAIL BARANG</h1>
@stop

@section('content')

<div class="row" style="text-transform: uppercase;">

    {{-- KOLOM KIRI — Info Barang --}}
    <div class="col-md-8" >

        <x-adminlte-card title="Informasi Barang" theme="lightblue" icon="fas fa-box">

            <div class="mb-3">
                <a href="{{ route('master-barang.export-pdf-show', $barang->id) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>

            {{-- tambahkan setelah tabel info barang --}}
            @if ($barang->barangMasukDetail->first() && $barang->barangMasukDetail->first()->barangMasuk)
                <div class="mt-3">
                    <a href="{{ route('barang-masuk.show', $barang->barangMasukDetail->first()->barangMasuk->id) }}"
                    class="btn btn-info btn-sm">
                        <i class="fas fa-file-invoice"></i>
                        Lihat Nota Barang Masuk
                    </a>
                </div>
            @endif

            <table class="table table-borderless">
                <tr>
                    <td width="35%"><strong>Kode Barang</strong></td>
                    <td>: {{ $barang->kode_barang }}</td>
                </tr>
                <tr>
                    <td><strong>Nama Barang</strong></td>
                    <td>: {{ $barang->nama_barang }}</td>
                </tr>
                {{-- <tr>
                    <td><strong>Kategori</strong></td>
                    <td>:
                        @if ($barang->kategori == 'oli_mesin')
                            <span class="badge badge-warning">Oli Mesin</span>
                        @elseif ($barang->kategori == 'filter_solar')
                            <span class="badge badge-info">Filter Solar</span>
                        @else
                            <span class="badge badge-secondary">Item Bebas</span>
                        @endif
                    </td>
                </tr> --}}
                <tr>
                    <td><strong>Lokasi Gudang</strong></td>
                    <td>{{ $barang->gudang }}</td>
                </tr>
                <tr>
                    <td><strong>Qty</strong></td>
                    <td>: {{ $barang->qty }} {{ $barang->satuan }}</td>
                </tr>
                <tr>
                    <td><strong>Isi per Satuan</strong></td>
                    <td>: {{ number_format($barang->qty_satuan) }} Pcs</td>
                </tr>
                <tr>
                    <td><strong>Stok Saat Ini</strong></td>
                    <td>:
                        <span class="{{ $barang->stok_saat_ini <= 5 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                            {{ number_format($barang->stok_saat_ini) }} Pcs
                        </span>
                        @if ($barang->stok_saat_ini <= 5)
                            <span class="badge badge-danger ml-1">Stok Menipis</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Harga Jual</strong></td>
                    <td>: Rp {{ number_format($barang->harga_jual, 0, ',', '.') }} / pcs</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Masuk</strong></td>
                    <td>: {{ \Carbon\Carbon::parse($barang->tanggal_masuk)->format('d-m-Y H:i') }}</td>
                </tr>
            </table>

        </x-adminlte-card>

        {{-- RIWAYAT KELUAR --}}
        <x-adminlte-card title="Riwayat Keluar" theme="danger" icon="fas fa-arrow-up">

            <table class="table table-bordered table-striped">
                <thead class="text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Bus</th>
                        <th>Plat Nomor</th>
                        <th>Qty Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barang->transaksiKeluarDetail as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($detail->transaksiKeluar->tanggal)->format('d-m-Y H:i') }}</td>
                        <td>{{ $detail->transaksiKeluar->bus->nama_bus }}</td>
                        <td>{{ $detail->transaksiKeluar->bus->plat_nomor }}</td>
                        <td class="text-center">{{ $detail->qty }} Pcs</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada riwayat keluar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </x-adminlte-card>

    </div>

    {{-- KOLOM KANAN — Foto & QR Code --}}
    <div class="col-md-4">

        {{-- Foto Barang --}}
        <x-adminlte-card title="Foto Barang" theme="lightblue" icon="fas fa-image">
            @if ($barang->foto)
                <img src="{{ asset('storage/' . $barang->foto) }}"
                     class="img-fluid"
                     style="border-radius:8px; width:100%">
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-image fa-3x mb-2"></i>
                    <p>Tidak ada foto</p>
                </div>
            @endif
        </x-adminlte-card>

        {{-- QR Code --}}
        <x-adminlte-card title="QR Code" theme="lightblue" icon="fas fa-qrcode">
            @if ($barang->qr_code)
                <div class="text-center">
                    <img src="{{ asset('storage/' . $barang->qr_code) }}"
                         class="img-fluid"
                         style="max-width:200px">
                    <p class="text-muted mt-2 mb-3">
                        <small>Scan untuk buka halaman ini</small>
                    </p>
                    <a href="{{ asset('storage/' . $barang->qr_code) }}"
                       download="qrcode-{{ $barang->kode_barang }}.svg"
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Download QR
                    </a>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-qrcode fa-3x mb-2"></i>
                    <p>QR Code belum dibuat</p>
                </div>
            @endif
        </x-adminlte-card>

    </div>

</div>

<a href="{{ route('master-barang.by-nama', $barang->nama_barang) }}" class="btn btn-secondary mb-3">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>

@stop