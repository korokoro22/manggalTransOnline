@extends('adminlte::page')

@section('title', 'Laporan Pergerakan Barang')

@section('content_header')
    <h1 style="text-transform: uppercase;">Laporan Barang</h1>
@stop

@section('content')

{{-- ACTION BUTTON --}}
<div class="mb-3">
    <a href="{{ route('laporan-barang.export-pdf', request()->query()) }}" class="btn btn-success">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
</div>

{{-- FILTER --}}
<x-adminlte-card title="Filter Laporan" theme="light" icon="fas fa-filter" style="text-transform: uppercase;">

    <form action="{{ route('laporan-barang.index') }}" method="GET">

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text"
                           name="nama_barang"
                           class="form-control"
                           placeholder="Cari nama barang..."
                           value="{{ $namaBarang }}"
                           style="text-transform: uppercase;">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal (spesifik)</label>
                    <input type="date"
                           name="tanggal"
                           class="form-control"
                           value="{{ $tanggal }}">
                    <small class="text-muted">Isi untuk cari per hari. Kosongkan untuk pakai bulan/tahun.</small>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>Bulan</label>
                    <select name="bulan" class="form-control">
                        <option value="">-- Bulan --</option>
                        @foreach (['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $label)
                            <option value="{{ $val }}" {{ (string) $bulan === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control">
                        <option value="">-- Tahun --</option>
                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}" {{ (string) $tahun === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>Tipe Pergerakan</label>
                    <select name="tipe" class="form-control">
                        <option value="semua" {{ $tipe === 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="masuk" {{ $tipe === 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="keluar" {{ $tipe === 'keluar' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Sub-tipe Keluar</label>
                    <select name="sub_tipe_keluar" class="form-control">
                        <option value="semua" {{ $subTipeKeluar === 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="per_item" {{ $subTipeKeluar === 'per_item' ? 'selected' : '' }}>Per Item</option>
                        <option value="paket_service" {{ $subTipeKeluar === 'paket_service' ? 'selected' : '' }}>Paket Service</option>
                    </select>
                    <small class="text-muted">Hanya berlaku jika Tipe Pergerakan = Keluar / Semua.</small>
                </div>
            </div>

        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> FILTER
            </button>
            <a href="{{ route('laporan-barang.index') }}" class="btn btn-secondary">
                Reset
            </a>
        </div>

    </form>

</x-adminlte-card>

{{-- RINGKASAN --}}
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Qty Masuk</span>
                <span class="info-box-number">{{ number_format($ringkasan['total_qty_masuk']) }} Pcs</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Qty Keluar</span>
                <span class="info-box-number">{{ number_format($ringkasan['total_qty_keluar']) }} Pcs</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Uang Keluar (Beli)</span>
                <span class="info-box-number">Rp {{ number_format($ringkasan['total_uang_keluar'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Uang Masuk (Jual)</span>
                <span class="info-box-number">Rp {{ number_format($ringkasan['total_uang_masuk'], 0, ',', '.') }}</span>
                <small class="text-muted d-block">*di luar transaksi Paket Service</small>
            </div>
        </div>
    </div>
</div>

{{-- TABEL PERGERAKAN --}}
<x-adminlte-card title="Riwayat Pergerakan Barang" theme="lightblue" icon="fas fa-exchange-alt" style="text-transform: uppercase;">

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Kode Barang</th>
                    <th>Tipe</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pergerakan as $index => $item)
                <tr>
                    <td class="text-center">{{ $pergerakan->firstItem() + $index }}</td>
                    <td class="text-center">
                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') : '-' }}
                    </td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td class="text-center">
                        @if ($item->tipe === 'masuk')
                            <span class="badge badge-success">{{ $item->tipe_label }}</span>
                        @elseif ($item->tipe === 'keluar_per_item')
                            <span class="badge badge-danger">{{ $item->tipe_label }}</span>
                        @else
                            <span class="badge badge-warning">{{ $item->tipe_label }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->qty) }} {{ $item->satuan }}</td>
                    <td class="text-right">
                        @if (is_null($item->subtotal))
                            <span class="text-muted">— (bagian dari paket)</span>
                        @else
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        @endif
                    </td>
                    <td>{{ $item->keterangan }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada pergerakan barang pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $pergerakan->links() }}
    </div>

</x-adminlte-card>

@stop