@extends('adminlte::page')

@section('title', 'Detail Barang — ' . $nama_barang)

@section('content_header')
    <h1 style="text-transform: uppercase;">{{ $nama_barang }}</h1>
@stop

@section('content')

{{-- ACTION BUTTONS --}}
<div class="row mb-3" style="text-transform: uppercase;">
    <div class="col-md-12">

        {{-- <a href="{{ route('master-barang.export-pdf-show-by-nama', $nama_barang) }}" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a> --}}
        <a href="{{ route('master-barang.export-pdf-show-by-nama', array_merge(['nama_barang' => $nama_barang], request()->query())) }}" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>

        <button class="btn btn-dark" data-toggle="modal" data-target="#modal-scan-qr">
            <i class="fas fa-qrcode"></i> SCAN QR CODE
        </button>

    </div>
</div>

{{-- FILTER SECTION --}}
<x-adminlte-card title="Filter Batch — {{ $nama_barang }}" theme="light" icon="fas fa-filter" style="text-transform: uppercase;">

    <form action="{{ route('master-barang.by-nama', $nama_barang) }}" method="GET">

        <div class="row">

            <div class="col-md-4">
                <label>Gudang</label>
                <select name="gudang" class="form-control" style="text-transform: uppercase;">
                    <option value="">-- Semua Gudang --</option>
                    <option value="gudang_utama" {{ request('gudang') == 'gudang_utama' ? 'selected' : '' }}>Gudang Utama</option>
                    <option value="gudang_2"     {{ request('gudang') == 'gudang_2'     ? 'selected' : '' }}>Gudang 2</option>
                    <option value="gudang_3"     {{ request('gudang') == 'gudang_3'     ? 'selected' : '' }}>Gudang 3</option>
                </select>
            </div>

            <div class="col-md-4">
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

            <div class="col-md-2">
                <label>Bulan Masuk</label>
                <select name="bulan" class="form-control" style="text-transform: uppercase;">
                    <option value="">-- Bulan --</option>
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

            <div class="col-md-2">
                <label>Tahun Masuk</label>
                <select name="tahun" class="form-control" style="text-transform: uppercase;">
                    <option value="">-- Tahun --</option>
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
            <a href="{{ route('master-barang.by-nama', $nama_barang) }}" class="btn btn-secondary">
                Reset
            </a>
        </div>

    </form>

</x-adminlte-card>

<x-adminlte-card title="Daftar Batch — {{ $nama_barang }}" theme="lightblue" icon="fas fa-boxes">

    <p class="text-muted">
        Ditemukan <strong>{{ $barangs->count() }}</strong> batch untuk barang dengan nama ini.
        Tiap batch berasal dari nota masuk yang berbeda.
    </p>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" style="text-transform: uppercase;">
            <thead class="text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Kode Barang</th>
                    <th>Foto</th>
                    <th>Kategori</th>
                    <th>Gudang</th>
                    <th>Stok Saat Ini</th>
                    <th>Harga Jual</th>
                    <th>Tanggal Masuk</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $index => $barang)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $barang->kode_barang }}</td>
                    <td class="text-center">
                        @if ($barang->foto)
                            <img src="{{ asset('storage/' . $barang->foto) }}"
                                 width="50"
                                 style="border-radius:8px"
                                 onclick="showPreview(event, '{{ asset('storage/' . $barang->foto) }}')">>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($barang->kategori == 'oli_mesin')
                            <span class="badge badge-warning">Oli Mesin</span>
                        @elseif ($barang->kategori == 'filter_solar')
                            <span class="badge badge-info">Filter Solar</span>
                        @else
                            <span class="badge badge-secondary">Item Bebas</span>
                        @endif
                    </td>
                    <td class="text-center">{{ str_replace('_', ' ', $barang->gudang) }}</td>
                    <td class="text-center">
                        <span class="{{ $barang->stok_saat_ini <= 5 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                            {{ number_format($barang->stok_saat_ini) }} Pcs
                        </span>
                        @if ($barang->stok_saat_ini <= 5)
                            <span class="badge badge-danger ml-1">Menipis</span>
                        @endif
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($barang->tanggal_masuk)->format('d-m-Y H:i') }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('master-barang.show', $barang->id) }}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Detail
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

</x-adminlte-card>

<a href="{{ route('master-barang.index') }}" class="btn btn-secondary mb-3">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>

{{-- Overlay Modal untuk Preview Gambar --}}
<div id="image-preview-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; justify-content: center; align-items: center; flex-direction: column;" onclick="if(event.target.id === 'image-preview-overlay') closePreview()">        <span onclick="closePreview()" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; user-select: none;">&times;</span>
    <img id="image-preview-src" src="" style="max-width: 90%; max-height: 85%; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
</div>

@section('js')

        <script>
            // ========== Fungsi Preview Gambar ==========
        function showPreview(event, src) {
            // Mencegah select2 menganggap ini sebagai klik item (mencegah dropdown tertutup)
            event.preventDefault();
            event.stopPropagation();
            
            const overlay = document.getElementById('image-preview-overlay');
            const img = document.getElementById('image-preview-src');
            img.src = src;
            overlay.style.display = 'flex';
        }

        function closePreview() {
            const overlay = document.getElementById('image-preview-overlay');
            overlay.style.display = 'none';
            document.getElementById('image-preview-src').src = '';
        }

        // ========== Inisialisasi Select2 Custom ==========
        function initSelect2Barang(row) {
            const select = $(row).find('.select-barang');
            if (!select.length) return;

            select.select2({
                dropdownParent: $(row),
                placeholder: '-- Cari nama barang atau kode barang --',
                allowClear: true,
                width: '100%',
                templateResult: function (option) {
                    if (!option.id || option.id === '') return option.text;

                    const b = barangs[option.id] || barangs[parseInt(option.id)];
                    if (!b) return option.text;

                    const foto = b.foto
                        ? `<img src="${b.foto}" class="select2-barang-foto" onmouseup="showPreview(event, '${b.foto}')" style="cursor: zoom-in;" title="Klik untuk memperbesar">`
                        : `<div class="select2-barang-foto-placeholder"><i class="fas fa-image"></i></div>`;

                    const stokClass = b.stok_saat_ini > 0 ? 'stok-ada' : 'stok-habis';
                    const stokText  = b.stok_saat_ini > 0
                        ? `Stok: ${b.stok_saat_ini} ${b.satuan}`
                        : 'Stok Habis';

                    const harga = Number(b.harga_jual).toLocaleString('id-ID');

                    let gudangText = b.gudang ? b.gudang.replace(/_/g, ' ') : '-';
                    gudangText = gudangText.replace(/\b\w/g, l => l.toUpperCase());

                    return $(`
                        <div class="select2-barang-option">
                            ${foto}
                            <div class="select2-barang-info">
                                <div class="nama">${b.nama_barang}</div>
                                <div class="baris-dua">
                                    <span><i class="fas fa-barcode"></i> ${b.kode_barang}</span>
                                    <span class="${stokClass}"><i class="fas fa-boxes"></i> ${stokText}</span>
                                    <span><i class="fas fa-warehouse"></i> ${gudangText}</span>
                                </div>
                                <div class="baris-tiga">
                                    <span><i class="fas fa-tag"></i> Rp ${harga} / pcs</span>
                                    <span><i class="fas fa-calendar"></i> ${b.tanggal_masuk}</span>
                                </div>
                            </div>
                        </div>
                    `);
                },
                templateSelection: function (option) {
                    if (!option.id) return option.text;
                    const b = barangs[option.id] || barangs[parseInt(option.id)];
                    if (!b) return option.text;
                    return `${b.nama_barang} (${b.kode_barang})`;
                }
            });

            // Trigger saat barang dipilih (Autofill Kode, Satuan, Harga Jual)
            select.on('change', function () {
                const b = barangs[$(this).val()];
                const r = $(this).closest('.item-row')[0];
                const info = r.querySelector('.foto-lama-info');
                const optLabel = r.querySelector('.foto-optional-label');
                const inputSatuan = r.querySelector('.input-satuan');
                const inputHargaJual = r.querySelector('.input-harga-jual');
                const inputHargaJualValue = r.querySelector('.input-harga-jual-value');
                const inputKodeBarang = r.querySelector('.input-kode-barang');
                
                if (b) {
                    if(info) info.style.display = 'inline';
                    if(optLabel) optLabel.style.display = 'inline';
                    
                    if(inputSatuan) inputSatuan.value = b.satuan;
                    if(inputKodeBarang) inputKodeBarang.value = b.kode_barang; // Set default kode_barang item lama
                    if(inputHargaJualValue) {
                        const hargaBersih = Math.round(parseFloat(b.harga_jual));
                        inputHargaJualValue.value = hargaBersih;
                        inputHargaJual.value = formatRupiah(hargaBersih);
                    }
                } else {
                    if(info) info.style.display = 'none';
                    if(optLabel) optLabel.style.display = 'none';
                    
                    if(inputSatuan) inputSatuan.value = '';
                    if(inputKodeBarang) inputKodeBarang.value = '-';
                    if(inputHargaJualValue) {
                        inputHargaJualValue.value = '';
                        inputHargaJual.value = '';
                    }
                }

                hitungSubtotalItem(r);
            });
        }
        </script>
        
    @stop

@stop