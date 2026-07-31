@extends('adminlte::page')

@section('title', 'Laporan Bus')

@section('content_header')
    <h1 style="text-transform: uppercase;">Laporan Bus</h1>
@stop

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="text-transform: uppercase;">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- ACTION BUTTON --}}
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('laporan-bus.export-pdf', request()->query()) }}" class="btn btn-success" style="text-transform: uppercase;">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>
</div>

{{-- FILTER --}}
<x-adminlte-card title="Filter Laporan Bus" theme="light" icon="fas fa-filter" style="text-transform: uppercase;">

    <form method="GET" action="{{ route('laporan-bus.index') }}">
        <div class="row">

            

            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal (spesifik)</label>
                    <input type="date"
                           name="tanggal"
                           class="form-control"
                           style="text-transform: uppercase;"
                           value="{{ request('tanggal') }}">
                    <small class="text-muted">Isi ini untuk cari per hari. Kosongkan untuk pakai filter bulan/tahun.</small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Bulan</label>
                    <select name="bulan" class="form-control" style="text-transform: uppercase;">
                        <option value="">-- Semua Bulan --</option>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                            <option value="{{ $i + 1 }}" {{ request('bulan') == $i + 1 ? 'selected' : '' }}>
                                {{ $bln }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control" style="text-transform: uppercase;">
                        <option value="">-- Semua Tahun --</option>
                        @foreach(range(date('Y'), 2024) as $t)
                            <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Bus</label>
                    <select name="bus_id" class="form-control" style="text-transform: uppercase;">
                        <option value="">-- Semua Bus --</option>
                        @foreach($busList as $bus)
                            <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                                {{ $bus->nama_bus }} - {{ $bus->plat_nomor }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3" style="text-transform: uppercase;">
                <label>Nama Barang</label>
                <input type="text"
                        style="text-transform: uppercase;"
                       name="nama_item"
                       class="form-control"
                       placeholder="Cari nama barang..."
                       value="{{ request('nama_item') }}">
            </div>

        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> FILTER
            </button>
            <a href="{{ route('laporan-bus.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

</x-adminlte-card>

{{-- TABLE --}}
{{-- <x-adminlte-card title="Daftar Pengeluaran Bus" theme="danger" icon="fas fa-bus" style="text-transform: uppercase;">

    <table class="table table-bordered table-striped" style="text-transform: uppercase;">
        <thead class="text-center">
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Nama Bus</th>
                <th>Plat Nomor</th>
                <th>Driver</th>
                <th>Item Transaksi</th>
                <th>Total</th>
                <th width="12%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $index => $transaksi)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                <td>{{ $transaksi->bus->nama_bus }}</td>
                <td>{{ $transaksi->bus->plat_nomor }}</td>
                <td>{{ $transaksi->bus->nama_driver }}</td>
                <td>
                    @forelse ($transaksi->details as $detail)
                        <span class="badge badge-light border text-dark">{{ $detail->nama_item }}</span>
                    @empty
                        <span class="text-muted">-</span>
                    @endforelse
                </td>
                <td class="text-right">
                    Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column" style="row-gap: 8px;">
                        <a href="{{ route('laporan-bus.show', $transaksi->bus->id) }}"
                        class="btn btn-info btn-sm">
                            Detail
                        </a>
                        <a href="{{ route('barang-keluar.edit', $transaksi->id) }}"
                        class="btn btn-warning btn-sm">
                            Edit
                        </a>
                    </div>
                    
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">Belum ada data pengeluaran</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Total Keseluruhan</strong></td>
                <td class="text-right">
                    <strong>Rp {{ number_format($transaksis->sum('total_transaksi'), 0, ',', '.') }}</strong>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</x-adminlte-card> --}}

{{-- TABLE --}}
<x-adminlte-card title="Daftar Pengeluaran Bus" theme="danger" icon="fas fa-bus" style="text-transform: uppercase;">

    <table class="table table-bordered table-striped" style="text-transform: uppercase;">
        <thead class="text-center">
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Kategori Nota</th> {{-- KOLOM BARU --}}
                <th>Nama Bus</th>
                <th>Plat Nomor</th>
                <th>Driver</th>
                <th>Item Transaksi</th>
                <th>Total</th>
                <th width="12%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $index => $transaksi)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                <td class="text-center">
                    <span class="badge badge-info">
                        {{ $transaksi->kategori === 'normal' ? 'Nota Bengkel' : str_replace('_', ' ', $transaksi->kategori) }}
                    </span>
                </td>
                <td>{{ $transaksi->bus->nama_bus }}</td>
                <td>{{ $transaksi->bus->plat_nomor }}</td>
                <td>{{ $transaksi->bus->nama_driver }}</td>
                <td>
                    @forelse ($transaksi->details as $detail)
                        <span class="badge badge-light border text-dark">{{ $detail->nama_item }}</span>
                    @empty
                        <span class="text-muted">-</span>
                    @endforelse
                </td>
                <td class="text-right">
                    Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column" style="row-gap: 8px;">
                        <a href="{{ route('laporan-bus.show', $transaksi->bus->id) }}"
                        class="btn btn-info btn-sm">
                            Detail
                        </a>
                        <a href="{{ route('barang-keluar.edit', $transaksi->id) }}"
                        class="btn btn-warning btn-sm">
                            Edit
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">Belum ada data pengeluaran</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right"><strong>Total Keseluruhan</strong></td>
                <td class="text-right">
                    <strong>Rp {{ number_format($transaksis->sum('total_transaksi'), 0, ',', '.') }}</strong>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</x-adminlte-card>

@stop