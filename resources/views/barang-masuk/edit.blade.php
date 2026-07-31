@extends('adminlte::page')

@section('title', 'Edit Barang Masuk')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container { width: 100% !important; }
    
    /* CSS Dropdown Detail Barang (Untuk item yang baru ditambahkan) */
    .select2-barang-option { display: flex; align-items: center; gap: 12px; padding: 6px 4px; }
    .select2-barang-foto { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; flex-shrink: 0; }
    .select2-barang-foto-placeholder { width: 60px; height: 60px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #aaa; font-size: 20px; }
    .select2-barang-info { flex: 1; }
    .select2-barang-info .nama { font-weight: bold; font-size: 13px; margin-bottom: 2px; }
    .select2-barang-info .baris-dua { display: flex; gap: 12px; font-size: 11px; color: #555; margin-bottom: 2px; }
    .select2-barang-info .baris-tiga { display: flex; gap: 12px; font-size: 11px; }
    .stok-ada { color: #28a745; font-weight: bold; }
    .stok-habis { color: #dc3545; font-weight: bold; }
    .select2-results__option { padding: 4px 6px; }

    /* CSS Umum Select2 */
    .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-selection__rendered {
        line-height: 38px !important;
    }
    .select2-selection__arrow {
        height: 38px !important;
    }
</style>
@stop

@section('content_header')
    <h1 style="text-transform: uppercase;">Edit Barang Masuk</h1>
@stop

@section('content')

<form action="{{ route('barang-masuk.update', $barangMasuk->id) }}" method="POST" enctype="multipart/form-data" style="text-transform: uppercase;">
@csrf
@method('PUT')

{{-- HEADER NOTA --}}
<x-adminlte-card title="Informasi Nota" theme="warning" icon="fas fa-file-invoice" style="text-transform: uppercase;">

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Tanggal Masuk</label>
                <input type="datetime-local" name="tanggal_masuk" class="form-control @error('tanggal_masuk') is-invalid @enderror" value="{{ old('tanggal_masuk', $barangMasuk->tanggal_masuk) }}" style="text-transform: uppercase;" required>
                @error('tanggal_masuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>No. Invoice</label>
                <input type="text" name="no_invoice" class="form-control @error('no_invoice') is-invalid @enderror" placeholder="Masukkan nomor invoice" value="{{ old('no_invoice', $barangMasuk->no_invoice) }}" style="text-transform: uppercase;" required>
                @error('no_invoice') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Supplier</label>
                <input type="text" name="supplier" class="form-control @error('supplier') is-invalid @enderror" placeholder="Masukkan nama supplier" value="{{ old('supplier', $barangMasuk->supplier) }}" style="text-transform: uppercase;" required>
                @error('supplier') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Penerima</label>
                <input type="text" name="penerima" class="form-control @error('penerima') is-invalid @enderror" placeholder="Masukkan nama penerima" value="{{ old('penerima', $barangMasuk->penerima) }}" style="text-transform: uppercase;" required>
                @error('penerima') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Bukti Nota</label>
                @if ($barangMasuk->bukti_nota)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $barangMasuk->bukti_nota) }}" width="80" style="border-radius:5px" onclick="showPreview(event, '{{ asset('storage/' . $barangMasuk->bukti_nota) }}')" title="Klik untuk memperbesar">
                        <small class="text-muted d-block mt-1">Upload baru untuk mengganti</small>
                    </div>
                @endif
                <input type="file" name="bukti_nota" class="form-control @error('bukti_nota') is-invalid @enderror" accept="image/*">
                @error('bukti_nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

</x-adminlte-card>

{{-- ITEM BARANG --}}
<x-adminlte-card title="Item Barang" theme="warning" icon="fas fa-boxes" style="text-transform: uppercase;">

    <small class="text-muted">Edit item barang dalam nota ini</small>

    <div class="mt-3" id="item-container">

        {{-- LOOPING ITEM BAWAAN (READONLY NAMA) --}}
        @foreach ($barangMasuk->details as $index => $detail)
        <div class="card card-outline card-secondary mb-3 item-row">
            <div class="card-header">
                <h6 class="card-title mb-0">Item #{{ $index + 1 }}</h6>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text"
                                name="items[{{ $index }}][kode_barang]"
                                class="form-control"
                                placeholder="Masukkan kode barang"
                                style="text-transform: uppercase;"
                                value="{{ old('items.' . $index . '.kode_barang', $detail->barang->kode_barang ?? '') }}"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="items[{{ $index }}][kategori]" class="form-control select-kategori" required style="text-transform: uppercase;">
                                <option value="">-- Pilih / Ketik Kategori --</option>
                                
                                {{-- Looping Kategori Dinamis dari Database --}}
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}" {{ old('items.' . $index . '.kategori', $detail->barang->kategori ?? '') == $kat ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $kat)) }}
                                    </option>
                                @endforeach
                                
                                {{-- Opsi Default Tambahan --}}
                                <option value="item_bebas" {{ old('items.' . $index . '.kategori', $detail->barang->kategori ?? '') == 'item_bebas' ? 'selected' : '' }}>
                                    Item Bebas
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        {{-- Nama Barang Readonly (Tidak bisa diedit dan tidak ada radio button) --}}
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text"
                                name="items[{{ $index }}][nama_barang]"
                                class="form-control"
                                placeholder="Masukkan nama barang"
                                style="text-transform: uppercase; background-color: #e9ecef; cursor: not-allowed;"
                                value="{{ old('items.' . $index . '.nama_barang', $detail->nama_barang) }}"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gudang</label>
                            <select name="items[{{ $index }}][gudang]" class="form-control" required style="text-transform: uppercase;">
                                <option value="">-- Pilih Gudang --</option>
                                <option value="gudang_utama" {{ old('items.' . $index . '.gudang', $detail->barang->gudang ?? '') == 'gudang_utama' ? 'selected' : '' }}>Gudang Utama</option>
                                <option value="gudang_2"     {{ old('items.' . $index . '.gudang', $detail->barang->gudang ?? '') == 'gudang_2'     ? 'selected' : '' }}>Gudang 2</option>
                                <option value="gudang_3"     {{ old('items.' . $index . '.gudang', $detail->barang->gudang ?? '') == 'gudang_3'     ? 'selected' : '' }}>Gudang 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                Foto Barang
                                <small class="text-muted">(opsional, kosongkan untuk pakai foto lama)</small>
                            </label>
                            @if ($detail->foto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $detail->foto) }}" width="60" style="border-radius:5px" onclick="showPreview(event, '{{ asset('storage/' . $detail->foto) }}')">
                                    <small class="text-muted d-block mt-1">Upload baru untuk mengganti</small>
                                </div>
                            @endif
                            <input type="file" name="items[{{ $index }}][foto]" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" name="items[{{ $index }}][qty]" class="form-control" placeholder="Contoh: 2" min="1" value="{{ old('items.' . $index . '.qty', $detail->qty) }}" style="text-transform: uppercase;" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Satuan</label>
                            <input type="text" name="items[{{ $index }}][satuan]" class="form-control" placeholder="Dus, Box, Lusin" value="{{ old('items.' . $index . '.satuan', $detail->satuan) }}" style="text-transform: uppercase;" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Total (Pcs)</label>
                            <input type="number" name="items[{{ $index }}][qty_satuan]" class="form-control" placeholder="Contoh: 24" min="1" value="{{ old('items.' . $index . '.qty_satuan', $detail->qty_satuan) }}" style="text-transform: uppercase;" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Harga Jual <small class="text-muted">(per pcs)</small></label>
                            <input type="number" name="items[{{ $index }}][harga_jual]" class="form-control" placeholder="Harga jual per pcs" min="0" value="{{ old('items.' . $index . '.harga_jual', $detail->harga_jual) }}" style="text-transform: uppercase;" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Subtotal <small class="text-muted">(isi manual)</small></label>
                            <input type="number" name="items[{{ $index }}][subtotal]" class="form-control" placeholder="Masukkan subtotal" min="0" value="{{ old('items.' . $index . '.subtotal', $detail->subtotal) }}" style="text-transform: uppercase;" required>
                        </div>
                    </div>
                </div>

                <button type="button"
                        class="btn btn-danger btn-sm btn-remove-item"
                        {{ $barangMasuk->details->count() === 1 ? 'disabled' : '' }}>
                    <i class="fas fa-trash"></i> Hapus Item
                </button>

            </div>
        </div>
        @endforeach

    </div>

    <button type="button" class="btn btn-success btn-sm" id="btn-add-item">
        <i class="fas fa-plus"></i> Tambah Item
    </button>

    <hr>

    <div class="row">
        <div class="col-md-4 offset-md-8">
            <div class="form-group">
                <label><strong>Total Keseluruhan</strong></label>
                <input type="number" name="total" class="form-control" placeholder="Masukkan total keseluruhan" value="{{ $barangMasuk->details->sum('subtotal') }}" min="0" required>
            </div>
        </div>
    </div>

</x-adminlte-card>

<div class="mb-3">
    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <button type="submit" class="btn btn-warning">
        <i class="fas fa-save"></i> UPDATE
    </button>
</div>

{{-- Overlay Modal untuk Preview Gambar --}}
<div id="image-preview-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; justify-content: center; align-items: center; flex-direction: column;" onclick="if(event.target.id === 'image-preview-overlay') closePreview()">
    <span onclick="closePreview()" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; user-select: none;">&times;</span>
    <img id="image-preview-src" src="" style="max-width: 90%; max-height: 85%; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
</div>

</form>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const barangs = @json($barangs);
    const kategoriList = @json($kategoriList);
    let itemIndex = {{ $barangMasuk->details->count() }};

    // ========== Fungsi Preview Gambar ==========
    function showPreview(event, src) {
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

    // ========== Format & Unformat Rupiah ==========
    function formatRupiah(angka) {
        angka = angka.toString().replace(/\D/g, '');
        if (!angka) return '';
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function unformatRupiah(str) {
        return str ? str.toString().replace(/\./g, '') : '';
    }

    // ========== Auto-format tampilan + sinkron ke hidden pasangannya (real-time) ==========
    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('input-rupiah') || e.target.readOnly) return;

        const cursorPos = e.target.selectionStart;
        const oldLength = e.target.value.length;

        e.target.value = formatRupiah(e.target.value);

        const newLength = e.target.value.length;
        const diff = newLength - oldLength;
        const newPos = Math.max(0, cursorPos + diff);
        e.target.setSelectionRange(newPos, newPos);

        const rawValue = unformatRupiah(e.target.value);

        if (e.target.classList.contains('input-harga-jual')) {
            const container = e.target.closest('.input-group');
            const hidden = container.querySelector('.input-harga-jual-value');
            if (hidden) hidden.value = rawValue;

            const row = e.target.closest('.item-row');
            if (row) hitungSubtotalItem(row);
        }
    });

    // ========== Build Template Baris Baru ==========
    function buildItemRow(index) {
        const barangOptions = Object.values(barangs).map(b =>
            `<option value="${b.id}">${b.nama_barang} ${b.kode_barang}</option>`
        ).join('');

        // Generate Kategori Dinamis
        const kategoriOptions = kategoriList.map(kat => {
            let label = kat.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            return `<option value="${kat}">${label}</option>`;
        }).join('');

        return `
        <div class="card card-outline card-secondary mb-3 item-row" style="text-transform: uppercase;">
            <div class="card-header">
                <h6 class="card-title mb-0">Item #${index + 1}</h6>
            </div>
            <div class="card-body" style="text-transform: uppercase;">

                <div class="form-group">
                    <label>Jenis Item</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input jenis-radio" type="radio" name="items[${index}][jenis]" value="barang_baru" checked>
                            <label class="form-check-label">Barang Baru</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input jenis-radio" type="radio" name="items[${index}][jenis]" value="barang_ada">
                            <label class="form-check-label">Barang Sudah Ada</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" name="items[${index}][kode_barang]" class="form-control input-kode-barang" placeholder="Masukkan kode barang" required style="text-transform: uppercase;" value="-">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="items[${index}][kategori]" class="form-control select-kategori" required style="text-transform: uppercase;">
                                <option value="">-- Pilih / Ketik Kategori --</option>
                                ${kategoriOptions}
                                <option value="item_bebas">Item Bebas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">

                        <div class="section-nama-baru">
                            <div class="form-group">
                                <label>Nama Barang</label>
                                <input type="text" name="items[${index}][nama_barang]" class="form-control input-nama-barang-baru" placeholder="Masukkan nama barang" style="text-transform: uppercase;">
                            </div>
                        </div>

                        <div class="section-nama-existing" style="display:none">
                            <div class="form-group">
                                <label>Pilih Barang</label>
                                <select name="items[${index}][barang_id]" class="form-control select-barang" style="text-transform: uppercase;">
                                    <option value="">-- Cari nama barang atau kode barang --</option>
                                    ${barangOptions}
                                </select>
                                <small class="text-muted foto-lama-info" style="display:none;">
                                    <i class="fas fa-image"></i> Foto batch sebelumnya akan dipakai jika tidak upload foto baru.
                                </small>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gudang</label>
                            <select name="items[${index}][gudang]" class="form-control" required style="text-transform: uppercase;">
                                <option value="">-- Pilih Gudang --</option>
                                <option value="gudang_utama">Gudang Utama</option>
                                <option value="gudang_2">Gudang 2</option>
                                <option value="gudang_3">Gudang 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                Foto Barang
                                <small class="text-muted foto-optional-label" style="display:none">(opsional, kosongkan untuk pakai foto lama)</small>
                            </label>
                            <input type="file" name="items[${index}][foto]" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" name="items[${index}][qty]" class="form-control input-qty" placeholder="Contoh: 2" min="1" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Satuan</label>
                            <input type="text" name="items[${index}][satuan]" class="form-control input-satuan" placeholder="Dus, Box, Lusin" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Total (Pcs)</label>
                            <input type="number" name="items[${index}][qty_satuan]" class="form-control input-qty-satuan" placeholder="Contoh: 24" min="1" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Harga Jual <small class="text-muted">(per pcs)</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="hidden" name="items[${index}][harga_jual]" class="input-harga-jual-value">
                                <input type="text" class="form-control input-harga-jual input-rupiah" placeholder="Harga jual per pcs" inputmode="numeric" autocomplete="off" style="text-transform: uppercase;" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Subtotal <small class="text-muted">(otomatis)</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="hidden" name="items[${index}][subtotal]" class="input-subtotal-value">
                                <input type="text" class="form-control input-subtotal input-rupiah" placeholder="Otomatis terisi" inputmode="numeric" autocomplete="off" style="text-transform: uppercase;" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                    <i class="fas fa-trash"></i> Hapus Item
                </button>

            </div>
        </div>
        `;
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

    // ========== Inisialisasi Kategori Bebas (Select2 Tags) ==========
    function initSelect2Kategori(row) {
        const selectKat = $(row).find('.select-kategori');
        if (!selectKat.length) return;

        selectKat.select2({
            tags: true, // Memungkinkan mengetik kategori baru
            dropdownParent: $(row),
            placeholder: '-- Pilih / Ketik Kategori --',
            allowClear: true,
            width: '100%'
        });
    }

    // Panggil saat document ready untuk baris yang sudah ada
    $(document).ready(function () {
        document.querySelectorAll('.item-row').forEach(row => {
            initSelect2Barang(row);
            initSelect2Kategori(row);
        });
    });

    // ========== Logic Toggle Jenis Radio ==========
    document.getElementById('item-container').addEventListener('change', function (e) {
        if (e.target.classList.contains('jenis-radio')) {
            const row = e.target.closest('.item-row');
            const sectionBaru     = row.querySelector('.section-nama-baru');
            const sectionExisting = row.querySelector('.section-nama-existing');
            const inputKodeBarang = row.querySelector('.input-kode-barang');

            if (e.target.value === 'barang_ada') {
                sectionBaru.style.display     = 'none';
                sectionExisting.style.display = 'block';
            } else {
                // Saat kembali ke "Barang Baru", reset kode barang ke "-"
                sectionBaru.style.display     = 'block';
                sectionExisting.style.display = 'none';
                if(inputKodeBarang) inputKodeBarang.value = '-';
                
                const select = row.querySelector('.select-barang');
                if(select) $(select).val('').trigger('change.select2');

                const optLabel = row.querySelector('.foto-optional-label');
                if(optLabel) optLabel.style.display = 'none';
            }
        }
    });

    // ========== Aksi Tambah & Hapus Item ==========
    document.getElementById('btn-add-item').addEventListener('click', function () {
        const container = document.getElementById('item-container');
        container.insertAdjacentHTML('beforeend', buildItemRow(itemIndex));
        itemIndex++;
        updateRemoveButtons();

        const rows = document.querySelectorAll('.item-row');
        const newRow = rows[rows.length - 1];
        initSelect2Barang(newRow);
        initSelect2Kategori(newRow); // Inisiasi kategori di baris baru
    });

    document.getElementById('item-container').addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-item')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
            hitungTotalKeseluruhan();
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row) => {
            const btn = row.querySelector('.btn-remove-item');
            if (btn) btn.disabled = rows.length === 1;
        });
    }

    // ========== Perhitungan Hibrida (Dukung Item Lama & Baru) ==========
    function hitungSubtotalItem(row) {
        const qtySatuanInput = row.querySelector('input[name$="[qty_satuan]"]');
        const qtySatuan = parseFloat(qtySatuanInput?.value) || 0;

        let hargaJual = 0;
        const hargaHidden = row.querySelector('.input-harga-jual-value');
        if (hargaHidden) {
            hargaJual = parseFloat(hargaHidden.value) || 0;
        } else {
            const hargaInput = row.querySelector('input[name$="[harga_jual]"]');
            hargaJual = parseFloat(hargaInput?.value) || 0;
        }

        const subtotal = qtySatuan * hargaJual;

        const subtotalHidden = row.querySelector('.input-subtotal-value');
        const subtotalVisible = row.querySelector('.input-subtotal');

        if (subtotalHidden && subtotalVisible) {
            subtotalHidden.value = subtotal;
            subtotalVisible.value = formatRupiah(subtotal);
        } else {
            const subtotalInput = row.querySelector('input[name$="[subtotal]"]');
            if (subtotalInput) subtotalInput.value = subtotal;
        }

        hitungTotalKeseluruhan();
    }

    function hitungTotalKeseluruhan() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(function (row) {
            const subtotalHidden = row.querySelector('.input-subtotal-value');
            if (subtotalHidden) {
                total += parseFloat(subtotalHidden.value) || 0;
            } else {
                const subtotalInput = row.querySelector('input[name$="[subtotal]"]');
                total += parseFloat(subtotalInput?.value) || 0;
            }
        });
        
        const totalInput = document.querySelector('input[name="total"]');
        if (totalInput) totalInput.value = total;
    }

    // Tangkap perubahan input QTY, Harga Jual Biasa, & Harga Jual Format Rupiah
    document.getElementById('item-container').addEventListener('input', function (e) {
        if (e.target.matches('input[name$="[qty_satuan]"]') || 
            e.target.matches('input[name$="[harga_jual]"]') || 
            e.target.matches('.input-harga-jual')) {
            const row = e.target.closest('.item-row');
            hitungSubtotalItem(row);
        }
    });

</script>
@stop

@stop