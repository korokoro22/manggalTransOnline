@extends('adminlte::page')

@section('title', 'Detail Laporan Bus')

@section('content_header')
    <h1 style="text-transform: uppercase;">Detail Laporan Bus</h1>
@stop

@section('content')

{{-- PROFIL BUS --}}
<x-adminlte-card title="Profil Bus" theme="info" icon="fas fa-bus" style="text-transform: uppercase;">

    <div class="row">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <td width="40%"><strong>Nama Bus</strong></td>
                    <td>: {{ $bus->nama_bus }}</td>
                </tr>
                <tr>
                    <td><strong>Plat Nomor</strong></td>
                    <td>: {{ $bus->plat_nomor }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <td width="40%"><strong>Driver</strong></td>
                    <td>: {{ $bus->nama_driver }}</td>
                </tr>
                <tr>
                    <td><strong>Rute Trayek</strong></td>
                    <td>: {{ $bus->rute_trayek ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mt-2">
        <a href="{{ route('laporan-bus.export-pdf-show', $bus->id) . '?' . http_build_query(request()->query()) }}"
           class="btn btn-success btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>

</x-adminlte-card>

{{-- FILTER --}}
<x-adminlte-card title="Filter Pengeluaran" theme="light" icon="fas fa-filter" style="text-transform: uppercase;">

    <form method="GET" action="{{ route('laporan-bus.show', $bus->id) }}">

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

            <div class="col-md-4" style="text-transform: uppercase;">
                <label>Nama Barang</label>
                <input type="text"
                        style="text-transform: uppercase;"
                       name="nama_item"
                       class="form-control"
                       placeholder="Cari nama barang..."
                       value="{{ request('nama_item') }}">
            </div>

            {{-- <div class="col-md-2 d-flex align-items-end">
                <div class="form-group w-100">
                    <button type="submit" class="btn btn-primary btn-block" style="text-transform: uppercase;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('laporan-bus.show', $bus->id) }}" class="btn btn-secondary btn-sm">
                        Reset
                    </a>
                </div>
                
            </div> --}}
            

        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> FILTER
            </button>
            <a href="{{ route('laporan-bus.show', $bus->id) }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

</x-adminlte-card>

{{-- PENGELUARAN PER BULAN --}}
@forelse ($grouped as $periodeKey => $transaksis)

    @php
        $periode    = \Carbon\Carbon::createFromFormat('Y-m', $periodeKey);
        $totalBulan = $transaksis->sum('total_transaksi');
    @endphp

    <x-adminlte-card style="text-transform: uppercase;" title="Pengeluaran {{ $periode->translatedFormat('F Y') }}" theme="danger" icon="fas fa-arrow-up">

        {{-- <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Tanggal</th>
                    <th>Item Transaksi</th>
                    <th>Total</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksis as $index => $transaksi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                    <td>
                        @foreach ($transaksi->details as $detail)
                            <span class="badge badge-light border text-dark">{{ $detail->nama_item }}</span>
                        @endforeach
                    </td>
                    <td class="text-right text-danger">
                        Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('barang-keluar.show', $transaksi->id) }}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('barang-keluar.edit', $transaksi->id) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total {{ $periode->translatedFormat('F Y') }}</strong></td>
                    <td class="text-right text-danger">
                        <strong>Rp {{ number_format($totalBulan, 0, ',', '.') }}</strong>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table> --}}
        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Tanggal</th>
                    <th>Kategori Nota</th> {{-- KOLOM BARU --}}
                    <th>Item Transaksi</th>
                    <th>Total</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksis as $index => $transaksi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">
                            {{ $transaksi->kategori === 'normal' ? 'Nota Bengkel' : str_replace('_', ' ', $transaksi->kategori) }}
                        </span>
                    </td>
                    <td>
                        @foreach ($transaksi->details as $detail)
                            <span class="badge badge-light border text-dark">{{ $detail->nama_item }}</span>
                        @endforeach
                    </td>
                    <td class="text-right text-danger">
                        Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}
                    </td>
                    {{-- <td class="text-center">
                        <a href="{{ route('barang-keluar.show', $transaksi->id) }}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('barang-keluar.edit', $transaksi->id) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td> --}}

                    <td class="text-center">
                        <a href="{{ $transaksi->kategori === 'normal' ? route('barang-keluar.show', $transaksi->id) : route('nota-jalan.show', $transaksi->id) }}"
                        class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ $transaksi->kategori === 'normal' ? route('barang-keluar.edit', $transaksi->id) : route('nota-jalan.edit', $transaksi->id) }}"
                        class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total {{ $periode->translatedFormat('F Y') }}</strong></td>
                    <td class="text-right text-danger">
                        <strong>Rp {{ number_format($totalBulan, 0, ',', '.') }}</strong>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

    </x-adminlte-card>

@empty
    <x-adminlte-card title="Pengeluaran" theme="danger" icon="fas fa-arrow-up">
        <p class="text-center text-muted">Belum ada data pengeluaran untuk bus ini</p>
    </x-adminlte-card>
@endforelse

{{-- TOTAL KESELURUHAN --}}
@if ($grouped->count() > 0)
<x-adminlte-card theme="secondary">
    <div class="d-flex justify-content-between align-items-center">
        <strong>Total Keseluruhan Pengeluaran</strong>
        <strong class="text-danger">
            Rp {{ number_format($grouped->flatten()->sum('total_transaksi'), 0, ',', '.') }}
        </strong>
    </div>
</x-adminlte-card>
@endif

<a href="{{ route('laporan-bus.index') }}" class="btn btn-secondary mb-3">
    <i class="fas fa-arrow-left"></i> Kembali
</a>

@stop