@extends('adminlte::page')

@section('title', 'Master Barang')

@section('content_header')
    <h1 style="text-transform: uppercase;">Master Barang</h1>
@stop

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
@endif

{{-- ACTION BUTTONS --}}
<div class="row mb-3" style="text-transform: uppercase;">
    <div class="col-md-12">

        <a href="{{ route('master-barang.export-pdf', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>

        {{-- <button class="btn btn-dark" data-toggle="modal" data-target="#modal-scan-qr">
            <i class="fas fa-qrcode"></i> SCAN QR CODE
        </button> --}}

    </div>
</div>

{{-- FILTER SECTION --}}
<x-adminlte-card title="Filter Data Barang" theme="light" icon="fas fa-filter" style="text-transform: uppercase;">

    <form action="{{ route('master-barang.index') }}" method="GET">

        <div class="row">

            <div class="col-md-3" style="text-transform: uppercase;">
                <label>Nama Barang</label>
                <input type="text"
                        style="text-transform: uppercase;"
                       name="nama_barang"
                       class="form-control"
                       placeholder="Cari nama barang..."
                       value="{{ request('nama_barang') }}">
            </div>

            {{-- <div class="col-md-3">
                <label>Gudang</label>
                <select name="gudang" class="form-control" style="text-transform: uppercase;">
                    <option value="">-- Semua Gudang --</option>
                    <option value="gudang_utama" {{ request('gudang') == 'gudang_utama' ? 'selected' : '' }}>Gudang Utama</option>
                    <option value="gudang_2"     {{ request('gudang') == 'gudang_2'     ? 'selected' : '' }}>Gudang 2</option>
                    <option value="gudang_3"     {{ request('gudang') == 'gudang_3'     ? 'selected' : '' }}>Gudang 3</option>
                </select>
            </div> --}}

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

            <div class="col-md-3">
                <label>Bulan Masuk</label>
                <select name="bulan" class="form-control" style="text-transform: uppercase;">
                    <option value="">-- Pilih Bulan --</option>
                    <option value="1"  {{ request('bulan') == '1'  ? 'selected' : '' }}>Januari</option>
                    <option value="2"  {{ request('bulan') == '2'  ? 'selected' : '' }}>Februari</option>
                    <option value="3"  {{ request('bulan') == '3'  ? 'selected' : '' }}>Maret</option>
                    <option value="4"  {{ request('bulan') == '4'  ? 'selected' : '' }}>April</option>
                    <option value="5"  {{ request('bulan') == '5'  ? 'selected' : '' }}>Mei</option>
                    <option value="6"  {{ request('bulan') == '6'  ? 'selected' : '' }}>Juni</option>
                    <option value="7"  {{ request('bulan') == '7'  ? 'selected' : '' }}>Juli</option>
                    <option value="8"  {{ request('bulan') == '8'  ? 'selected' : '' }}>Agustus</option>
                    <option value="9"  {{ request('bulan') == '9'  ? 'selected' : '' }}>September</option>
                    <option value="10" {{ request('bulan') == '10' ? 'selected' : '' }}>Oktober</option>
                    <option value="11" {{ request('bulan') == '11' ? 'selected' : '' }}>November</option>
                    <option value="12" {{ request('bulan') == '12' ? 'selected' : '' }}>Desember</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Tahun Masuk</label>
                <select name="tahun" class="form-control" style="text-transform: uppercase;">
                    <option value="">-- Pilih Tahun --</option>
                    @for ($i = now()->year; $i >= now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> FILTER
            </button>
            <a href="{{ route('master-barang.index') }}" class="btn btn-secondary">
                Reset
            </a>
        </div>

    </form>

</x-adminlte-card>


{{-- TABLE SECTION --}}
<x-adminlte-card title="Daftar Master Barang" theme="lightblue" icon="fas fa-box" style="text-transform: uppercase;">

    <div class="table-responsive">
        <table class="table table-bordered table-striped">

            <thead class="text-center">
    <tr>
        <th>No</th>
        {{-- <th>Kode Barang</th>
        <th>Foto</th> --}}
        <th>Nama Barang</th>
        {{-- <th>Gudang</th> --}}
        <th>Jumlah Batch</th>
        <th>Total Stok</th>
        <th>Rentang Harga</th>
        <th>Tanggal Masuk Terakhir</th>
        <th width="10%">Aksi</th>
    </tr>
</thead>

            <tbody>

    @forelse ($barangs as $index => $item)
        @php
            $sample = $samples[$item->nama_barang] ?? null;
        @endphp
    <tr>
        <td class="text-center">{{ $barangs->firstItem() + $index }}</td>
        {{-- <td>{{ $sample->kode_barang ?? '-' }}</td>
        <td class="text-center">
            @if ($sample && $sample->foto)
                <img src="{{ asset('storage/' . $sample->foto) }}"
                     width="50"
                     style="border-radius:8px">
            @else
                <span class="text-muted">-</span>
            @endif
        </td> --}}
        <td>{{ $item->nama_barang }}</td>
        {{-- <td class="text-center">
            @php
                $labelGudang = match($sample->gudang ?? null) {
                    'gudang_utama' => ['label' => 'Gudang Utama', 'color' => 'primary'],
                    'gudang_2'     => ['label' => 'Gudang 2',     'color' => 'warning'],
                    'gudang_3'     => ['label' => 'Gudang 3',     'color' => 'success'],
                    default        => ['label' => '-',             'color' => 'secondary'],
                };
            @endphp
            <span class="badge badge-{{ $labelGudang['color'] }}">{{ $labelGudang['label'] }}</span>
        </td> --}}
        <td class="text-center">
            <span class="badge badge-dark">{{ $item->jumlah_batch }} Batch</span>
        </td>
        <td class="text-center">
            <span class="{{ $item->total_stok <= 5 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                {{ number_format($item->total_stok) }} Pcs
            </span>
            @if ($item->total_stok <= 5)
                <span class="badge badge-danger">Menipis</span>
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
        <td class="text-center">
            <a href="{{ route('master-barang.by-nama', $item->nama_barang) }}"
               class="btn btn-info btn-sm">
                Detail
            </a>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="10" class="text-center">Belum ada data barang</td>
    </tr>
    @endforelse

</tbody>

        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $barangs->links() }}
    </div>


</x-adminlte-card>


{{-- MODAL: SCAN QR CODE --}}
{{-- <div class="modal fade" id="modal-scan-qr" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode mr-1"></i> Scan QR Code
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted">Fitur scan QR Code akan segera tersedia.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div> --}}

{{-- MODAL: SCAN QR CODE --}}
{{-- <div class="modal fade" id="modal-scan-qr" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode mr-1"></i> Scan QR Code
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width:100%"></div>
                <div id="qr-result" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div> --}}

{{-- MODAL: SCAN QR CODE --}}
<div class="modal fade" id="modal-scan-qr" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode mr-1"></i> Scan QR Code
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width:100%"></div>
                <div id="qr-result" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrCode = null;

    // Saat modal dibuka → start kamera
    $('#modal-scan-qr').on('shown.bs.modal', function () {
        html5QrCode = new Html5Qrcode("qr-reader");

        html5QrCode.start(
            { facingMode: "environment" }, // kamera belakang
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            // function (decodedText, decodedResult) {
            //     // Berhasil scan — stop kamera lalu redirect
            //     html5QrCode.stop().then(() => {
            //         $('#modal-scan-qr').modal('hide');
            //         window.location.href = decodedText;
            //     });
            // },
            function (decodedText, decodedResult) {
                html5QrCode.stop().then(() => {
                    $('#modal-scan-qr').modal('hide');

                    // Handle QR lama (URL) dan baru (angka murni)
                    const id = decodedText.includes('/')
                        ? decodedText.split('/').pop()
                        : decodedText;

                    window.location.href = "{{ route('master-barang.show', ':id') }}".replace(':id', id);
                });
            },
            function (errorMessage) {
                // Tidak perlu ditampilkan — ini terpanggil tiap frame gagal scan
            }
        ).catch(function (err) {
            $('#qr-result').html(
                '<div class="alert alert-danger">Tidak bisa mengakses kamera: ' + err + '</div>'
            );
        });
    });

    // Saat modal ditutup → stop kamera
    $('#modal-scan-qr').on('hidden.bs.modal', function () {
        if (html5QrCode) {
            html5QrCode.stop().catch(() => {});
            html5QrCode = null;
        }
    });
</script>
@stop