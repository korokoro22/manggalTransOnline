@extends('adminlte::page')

@section('title', 'Tambah Transaksi Keluar')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container { width: 100% !important; }
    .select2-barang-option { 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        padding: 6px 4px; 
    }
    .select2-barang-foto { 
        width: 60px; 
        height: 60px; 
        object-fit: cover; 
        border-radius: 5px; 
        flex-shrink: 0; 
    }
    .select2-barang-foto-placeholder { 
        width: 60px; 
        height: 60px; 
        background: #f0f0f0; 
        border-radius: 5px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        flex-shrink: 0; 
        color: #aaa; 
        font-size: 20px;
    }
    .select2-barang-info { flex: 1; }
    .select2-barang-info .nama { 
        font-weight: bold; 
        font-size: 13px; 
        margin-bottom: 2px;
    }
    .select2-barang-info .baris-dua {
        display: flex;
        gap: 12px;
        font-size: 11px;
        color: #555;
        margin-bottom: 2px;
    }
    .select2-barang-info .baris-tiga {
        display: flex;
        gap: 12px;
        font-size: 11px;
    }
    .stok-ada { color: #28a745; font-weight: bold; }
    .stok-habis { color: #dc3545; font-weight: bold; }
    .select2-results__option { padding: 4px 6px; }
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
    <h1 style="text-transform: uppercase;">Tambah Transaksi Keluar</h1>
@stop

@section('content')

@if(session('error'))
    <div class="alert alert-danger" style="text-transform: uppercase;">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" style="text-transform: uppercase;">
        <i class="fas fa-exclamation-triangle"></i> <strong>Terjadi kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-adminlte-card title="Form Transaksi Keluar" theme="danger" icon="fas fa-plus-circle" style="text-transform: uppercase;">

    <form action="{{ route('barang-keluar.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bus</label>
                    <select name="bus_id" id="bus-select" class="form-control" required style="text-transform: uppercase;">
                        <option value="">-- Pilih Bus --</option>
                        @foreach($busList as $bus)
                            <option value="{{ $bus->id }}">
                                {{ $bus->nama_bus }} - {{ $bus->plat_nomor }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="datetime-local" name="tanggal" class="form-control" required style="text-transform: uppercase;">
                </div>
            </div>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-0"><i class="fas fa-list"></i> Item Transaksi</h5>
                <small class="text-muted">Tambahkan item transaksi, bisa lebih dari satu dalam satu transaksi</small>
            </div>
            <button type="button" class="btn btn-info btn-sm" id="btn-scan-qr">
                <i class="fas fa-qrcode"></i> SCAN QR BARANG
            </button>
        </div>

        <div class="mt-3" id="item-container">

            <div class="card card-outline card-secondary mb-3 item-row">
                <div class="card-body">

                    {{-- Tipe --}}
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tipe Item</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input tipe-radio"
                                               type="radio"
                                               name="items[0][tipe]"
                                               value="per_item"
                                               checked>
                                        <label class="form-check-label">Per Item</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input tipe-radio"
                                               type="radio"
                                               name="items[0][tipe]"
                                               value="paket_service">
                                        <label class="form-check-label">Paket Service</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input tipe-radio"
                                            type="radio"
                                            name="items[0][tipe]"
                                            value="biaya_pengerjaan">
                                        <label class="form-check-label">Biaya Pengerjaan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input FINAL subtotal — inilah yang benar-benar dibaca controller --}}
                    <input type="hidden" name="items[0][subtotal]" class="input-subtotal-final">

                    {{-- Section Per Item --}}
                    <div class="section-per-item">

                        {{-- Pilih Barang full width --}}
                        <div class="form-group">
                            <label>Pilih Barang</label>
                            <select name="items[0][barang_id]" class="form-control select-barang" style="text-transform: uppercase;">
                                <option value="">-- Cari nama barang atau kode barang --</option>
                                @foreach($barangs as $barang)
                                    <option value="{{ $barang['id'] }}">
                                        {{ $barang['nama_barang'] }} {{ $barang['kode_barang'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Satuan</label>
                                    <input type="text"
                                           name="items[0][satuan]"
                                           class="form-control input-satuan"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Harga Satuan</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[0][harga_satuan]" class="input-harga-satuan-value">
                                        <input  type="text"
                                            class="form-control input-harga-satuan input-rupiah"
                                            placeholder="Otomatis terisi"
                                            style="text-transform: uppercase;"
                                            inputmode="numeric"
                                            autocomplete="off"
                                            >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Qty</label>
                                    <input type="number"
                                           name="items[0][qty]"
                                           class="form-control input-qty"
                                           placeholder="Masukkan qty"
                                           style="text-transform: uppercase;"
                                           min="1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Subtotal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[0][subtotal_per_item]" class="input-subtotal-value">
                                        <input type="text"
                                           class="form-control input-subtotal input-rupiah"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           inputmode="numeric"
                                           autocomplete="off"
                                           readonly
                                           >
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Section Paket Service --}}
                    <div class="section-paket-service" style="display:none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paket Service</label>
                                    <select name="items[0][paket_service_id]"
                                            class="form-control select-paket"
                                            style="text-transform: uppercase;"
                                            disabled>
                                        <option value="">-- Pilih Bus dulu --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Harga Paket</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text"
                                           class="form-control input-harga-paket input-rupiah"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           inputmode="numeric"
                                           autocomplete="off"
                                           readonly>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Subtotal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[0][subtotal_paket]" class="input-subtotal-value">
                                        <input type="text"
                                            class="form-control input-subtotal input-rupiah"
                                            placeholder="Otomatis terisi"
                                            style="text-transform: uppercase;"
                                            autocomplete="off"
                                            inputmode="numeric"
                                            readonly
                                            >
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section Biaya Pengerjaan --}}
                    <div class="section-biaya-pengerjaan" style="display:none">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Keterangan Pengerjaan</label>
                                    <input type="text"
                                        name="items[0][keterangan]"
                                        class="form-control input-keterangan-pengerjaan"
                                        style="text-transform: uppercase;"
                                        placeholder="Contoh: Servis rem, ganti oli mesin, dll">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Biaya Pengerjaan</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[0][biaya_pengerjaan]" class="input-biaya-pengerjaan-value">
                                        <input type="text"
                                        class="form-control input-biaya-pengerjaan input-rupiah"
                                        placeholder="Masukkan biaya"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        style="text-transform: uppercase;"
                                        >
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Subtotal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[0][subtotal_biaya]" class="input-subtotal-value">
                                        <input type="text"
                                            class="form-control input-subtotal input-rupiah"
                                            placeholder="Otomatis terisi"
                                            style="text-transform: uppercase;"
                                            autocomplete="off"
                                            inputmode="numeric"
                                            readonly
                                            >
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-danger btn-sm btn-remove-item" disabled>
                        <i class="fas fa-trash"></i> Hapus Item
                    </button>

                </div>
            </div>

        </div>

        <button type="button" class="btn btn-success btn-sm mb-3" id="btn-add-item">
            <i class="fas fa-plus"></i> Tambah Item
        </button>

        <hr>

        <div class="row">
            <div class="col-md-4 offset-md-8">
                <div class="form-group">
                    <label><strong>Total Transaksi</strong></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="hidden" name="total_transaksi" id="total-transaksi-hidden">
                        <input type="text"
                           id="total-transaksi"
                           class="form-control input-rupiah"
                           placeholder="Rp 0"
                           readonly
                           >
                    </div>
                    
                </div>
            </div>
        </div>

        <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> KEMBALI
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> SIMPAN
        </button>

    </form>

    {{-- Overlay Modal untuk Preview Gambar --}}
    <div id="image-preview-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; justify-content: center; align-items: center; flex-direction: column;" onclick="if(event.target.id === 'image-preview-overlay') closePreview()">
        <span onclick="closePreview()" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; user-select: none;">&times;</span>
        <img id="image-preview-src" src="" style="max-width: 90%; max-height: 85%; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
    </div>

</x-adminlte-card>

{{-- Modal Scanner --}}
<div class="modal fade" id="modal-scanner" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-qrcode"></i> SCAN QR BARANG</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width:100%"></div>
                <div id="qr-scan-status" class="mt-2 text-muted small">
                    Arahkan kamera ke QR code barang...
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Barang --}}
<div class="modal fade" id="modal-konfirmasi-barang" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-box"></i> Hasil Scan Barang</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="konfirmasi-foto" src="" alt="Foto Barang"
                         style="max-height:150px; object-fit:contain; display:none"
                         class="img-thumbnail">
                    <div id="konfirmasi-no-foto" class="text-muted small" style="display:none">
                        <i class="fas fa-image fa-2x"></i><br>Tidak ada foto
                    </div>
                </div>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Kode Barang</th>
                        <td id="konfirmasi-kode"></td>
                    </tr>
                    <tr>
                        <th>Nama Barang</th>
                        <td id="konfirmasi-nama"></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td id="konfirmasi-kategori"></td>
                    </tr>
                    <tr>
                        <th>Harga Satuan</th>
                        <td id="konfirmasi-harga"></td>
                    </tr>
                    <tr>
                        <th>Stok Saat Ini</th>
                        <td id="konfirmasi-stok"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a href="#" id="btn-lihat-detail" class="btn btn-info" target="_blank">
                    <i class="fas fa-eye"></i> Lihat Detail
                </a>
                <button type="button" class="btn btn-primary" id="btn-tambah-ke-form">
                    <i class="fas fa-plus"></i> Tambahkan ke Form
                </button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let itemIndex = 1;

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

    let paketList = [];

    function formatRupiah(angka) {
        angka = angka.toString().replace(/\D/g, '');
        if (!angka) return '';
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function unformatRupiah(str) {
        return str ? str.toString().replace(/\./g, '') : '';
    }

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
        const container = e.target.closest('.input-group') || e.target.closest('.item-row') || e.target.closest('form');

        if (e.target.classList.contains('input-harga-satuan')) {
            const hidden = container.querySelector('.input-harga-satuan-value');
            if (hidden) hidden.value = rawValue;

            const row = e.target.closest('.item-row');
            if (row) {
                const qty      = parseInt(row.querySelector('.input-qty')?.value) || 0;
                const harga    = parseFloat(rawValue) || 0;
                const subtotal = qty * harga;
                row.querySelector('.section-per-item .input-subtotal').value       = formatRupiah(subtotal);
                row.querySelector('.section-per-item .input-subtotal-value').value = subtotal;
                hitungTotal();
            }
        }

        if (e.target.classList.contains('input-biaya-pengerjaan')) {
            const hidden = container.querySelector('.input-biaya-pengerjaan-value');
            if (hidden) hidden.value = rawValue;

            const row = e.target.closest('.item-row');
            if (row) {
                const biaya = parseFloat(rawValue) || 0;
                row.querySelector('.section-biaya-pengerjaan .input-subtotal').value       = formatRupiah(biaya);
                row.querySelector('.section-biaya-pengerjaan .input-subtotal-value').value = biaya;
                hitungTotal();
            }
        }
    });

    const ajaxUrl   = "{{ route('barang-keluar.paket-by-bus', ':busId') }}";
    const jsonUrl   = "{{ route('master-barang.json', ':id') }}";
    const detailUrl = "{{ route('master-barang.show', ':id') }}";
    const barangs   = @json($barangs);

    function initSelect2Barang(row) {
        const select = $(row).find('.select-barang');

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

        select.on('change', function () {
            const barangId = $(this).val();
            const b = barangs[parseInt(barangId)] || barangs[barangId];
            const r = $(this).closest('.item-row')[0];

            if (b) {
                r.querySelector('.input-harga-satuan').value       = formatRupiah(b.harga_jual);
                r.querySelector('.input-harga-satuan-value').value = b.harga_jual;
                r.querySelector('.input-satuan').value             = b.satuan;
            } else {
                r.querySelector('.input-harga-satuan').value       = '';
                r.querySelector('.input-harga-satuan-value').value = 0;
                r.querySelector('.input-satuan').value             = '';
            }
            r.querySelector('.input-qty').value = '';
            r.querySelector('.section-per-item .input-subtotal').value       = formatRupiah(0);
            r.querySelector('.section-per-item .input-subtotal-value').value = 0;
            hitungTotal();
        });
    }

    document.getElementById('bus-select').addEventListener('change', function () {
        const busId = this.value;
        paketList = [];

        document.querySelectorAll('.select-paket').forEach(sel => {
            sel.innerHTML = '<option value="">-- Pilih Paket Service --</option>';
            sel.disabled = true;
        });

        if (!busId) return;

        fetch(ajaxUrl.replace(':busId', busId))
            .then(res => res.json())
            .then(data => {
                paketList = data;
                document.querySelectorAll('.select-paket').forEach(sel => {
                    sel.disabled = false;
                    data.forEach(paket => {
                        const opt         = document.createElement('option');
                        opt.value         = paket.id;
                        opt.text          = paket.nama_paket + ' — Rp ' + Number(paket.harga).toLocaleString('id-ID');
                        opt.dataset.harga = paket.harga;
                        sel.appendChild(opt);
                    });
                });
            });
    });

    function buildItemRow(index) {
        const barangOptions = Object.values(barangs).map(b =>
            `<option value="${b.id}">${b.nama_barang} ${b.kode_barang}</option>`
        ).join('');

        const paketOptions = paketList.length
            ? paketList.map(p => `<option value="${p.id}" data-harga="${p.harga}">${p.nama_paket} — Rp ${Number(p.harga).toLocaleString('id-ID')}</option>`).join('')
            : '';

        return `
        <div class="card card-outline card-secondary mb-3 item-row">
            <div class="card-body">

                <div class="row mb-2" style="text-transform: uppercase;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Tipe Item</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="per_item" checked style="text-transform: uppercase;">
                                    <label class="form-check-label">Per Item</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="paket_service" style="text-transform: uppercase;">
                                    <label class="form-check-label">Paket Service</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="biaya_pengerjaan" style="text-transform: uppercase;">
                                    <label class="form-check-label">Biaya Pengerjaan</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="items[${index}][subtotal]" class="input-subtotal-final">

                <div class="section-per-item">
                    <div class="form-group">
                        <label>Pilih Barang</label>
                        <select name="items[${index}][barang_id]" class="form-control select-barang" style="text-transform: uppercase;">
                            <option value="">-- Cari nama barang atau kode barang --</option>
                            ${barangOptions}
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Satuan</label>
                                <input type="text" name="items[${index}][satuan]" class="form-control input-satuan" placeholder="Otomatis terisi" readonly style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Harga Satuan</label>
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[${index}][harga_satuan]" class="input-harga-satuan-value">
                                        <input  type="text"
                                            class="form-control input-harga-satuan input-rupiah"
                                            placeholder="Otomatis terisi"
                                            style="text-transform: uppercase;"
                                            inputmode="numeric"
                                            autocomplete="off"
                                            >
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" name="items[${index}][qty]" class="form-control input-qty" placeholder="Masukkan qty" min="1" style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Subtotal</label>
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[${index}][subtotal_per_item]" class="input-subtotal-value">
                                        <input type="text"
                                           class="form-control input-subtotal input-rupiah"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           inputmode="numeric"
                                           autocomplete="off"
                                           readonly
                                           >
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-paket-service" style="display:none">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Paket Service</label>
                                <select name="items[${index}][paket_service_id]" class="form-control select-paket" ${paketList.length ? '' : 'disabled'} style="text-transform: uppercase;">
                                    <option value="">${paketList.length ? '-- Pilih Paket Service --' : '-- Pilih Bus dulu --'}</option>
                                    ${paketOptions}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Harga Paket</label>
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text"
                                           class="form-control input-harga-paket input-rupiah"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           inputmode="numeric"
                                           autocomplete="off"
                                           readonly>
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Subtotal</label>
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[${index}][subtotal_paket]" class="input-subtotal-value">
                                        <input type="text"
                                            class="form-control input-subtotal input-rupiah"
                                            placeholder="Otomatis terisi"
                                            style="text-transform: uppercase;"
                                            autocomplete="off"
                                            inputmode="numeric"
                                            readonly
                                            >
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-biaya-pengerjaan" style="display:none">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Keterangan Pengerjaan</label>
                                <input type="text" name="items[${index}][keterangan]" class="form-control input-keterangan-pengerjaan" placeholder="Contoh: Servis rem, ganti oli mesin, dll" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Biaya Pengerjaan</label>
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[${index}][biaya_pengerjaan]" class="input-biaya-pengerjaan-value">
                                        <input type="text"
                                        class="form-control input-biaya-pengerjaan input-rupiah"
                                        placeholder="Masukkan biaya"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        style="text-transform: uppercase;"
                                        >
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Subtotal</label>
                                <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="hidden" name="items[${index}][subtotal_biaya]" class="input-subtotal-value">
                                        <input type="text"
                                            class="form-control input-subtotal input-rupiah"
                                            placeholder="Otomatis terisi"
                                            style="text-transform: uppercase;"
                                            autocomplete="off"
                                            inputmode="numeric"
                                            readonly
                                            >
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                    <i class="fas fa-trash"></i> Hapus Item
                </button>
            </div>
        </div>`;
    }

    $(document).ready(function () {
        initSelect2Barang(document.querySelector('.item-row'));
    });

    document.getElementById('item-container').addEventListener('change', function (e) {
        const row = e.target.closest('.item-row');
        if (!row) return;

        if (e.target.classList.contains('tipe-radio')) {
            const sectionPaket  = row.querySelector('.section-paket-service');
            const sectionItem   = row.querySelector('.section-per-item');
            const sectionBiaya  = row.querySelector('.section-biaya-pengerjaan');

            sectionPaket.style.display = 'none';
            sectionItem.style.display  = 'none';
            sectionBiaya.style.display = 'none';

            if (e.target.value === 'paket_service') {
                sectionPaket.style.display = 'block';
            } else if (e.target.value === 'biaya_pengerjaan') {
                sectionBiaya.style.display = 'block';
            } else {
                sectionItem.style.display = 'block';
            }
            resetSubtotal(row);
        }

        if (e.target.classList.contains('select-paket')) {
            const harga = e.target.selectedOptions[0]?.dataset.harga || 0;
            row.querySelector('.input-harga-paket').value = formatRupiah(harga);
            row.querySelector('.section-paket-service .input-subtotal').value       = formatRupiah(harga);
            row.querySelector('.section-paket-service .input-subtotal-value').value = harga;
            hitungTotal();
        }
    });

    document.getElementById('item-container').addEventListener('input', function (e) {
        const row = e.target.closest('.item-row');
        if (!row) return;

        if (e.target.classList.contains('input-qty')) {
            const qty      = parseInt(row.querySelector('.input-qty').value) || 0;
            const harga    = parseFloat(unformatRupiah(row.querySelector('.input-harga-satuan').value)) || 0;
            const subtotal = qty * harga;
            row.querySelector('.section-per-item .input-subtotal').value       = formatRupiah(subtotal);
            row.querySelector('.section-per-item .input-subtotal-value').value = subtotal;
            hitungTotal();
        }
    });

    document.getElementById('item-container').addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-item')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
            hitungTotal();
        }
    });

    document.getElementById('btn-add-item').addEventListener('click', function () {
        document.getElementById('item-container')
                .insertAdjacentHTML('beforeend', buildItemRow(itemIndex));
        itemIndex++;
        updateRemoveButtons();
        const rows = document.querySelectorAll('.item-row');
        initSelect2Barang(rows[rows.length - 1]);
    });

    function updateSubtotalFinal(row) {
        const tipe = row.querySelector('.tipe-radio:checked')?.value;
        let subtotalValue = 0;

        if (tipe === 'paket_service') {
            subtotalValue = parseFloat(row.querySelector('.section-paket-service .input-subtotal-value')?.value) || 0;
        } else if (tipe === 'biaya_pengerjaan') {
            subtotalValue = parseFloat(row.querySelector('.section-biaya-pengerjaan .input-subtotal-value')?.value) || 0;
        } else {
            subtotalValue = parseFloat(row.querySelector('.section-per-item .input-subtotal-value')?.value) || 0;
        }

        row.querySelector('.input-subtotal-final').value = subtotalValue;
        return subtotalValue;
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            total += updateSubtotalFinal(row);
        });
        document.getElementById('total-transaksi').value = formatRupiah(total);
        document.getElementById('total-transaksi-hidden').value = total;
    }

    function resetSubtotal(row) {
        row.querySelectorAll('.input-subtotal').forEach(el => el.value = formatRupiah(0));
        row.querySelectorAll('.input-subtotal-value').forEach(el => el.value = 0);
        hitungTotal();
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            row.querySelector('.btn-remove-item').disabled = rows.length === 1;
        });
    }

    let html5QrCode  = null;
    let scannedBarang = null;

    document.getElementById('btn-scan-qr').addEventListener('click', function () {
        $('#modal-scanner').modal('show');
    });

    $('#modal-scanner').on('shown.bs.modal', function () {
        html5QrCode = new Html5Qrcode("qr-reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function (decodedText) {
                html5QrCode.stop().then(() => {
                    $('#modal-scanner').modal('hide');
                });

                const id = decodedText.includes('/')
                    ? decodedText.split('/').pop()
                    : decodedText;

                fetch(jsonUrl.replace(':id', id))
                    .then(res => {
                        if (!res.ok) throw new Error('Barang tidak ditemukan');
                        return res.json();
                    })
                    .then(data => {
                        scannedBarang = data;

                        document.getElementById('konfirmasi-kode').textContent     = data.kode_barang;
                        document.getElementById('konfirmasi-nama').textContent     = data.nama_barang;
                        document.getElementById('konfirmasi-kategori').textContent = data.kategori;
                        document.getElementById('konfirmasi-harga').textContent    = 'Rp ' + Number(data.harga_jual).toLocaleString('id-ID');
                        document.getElementById('konfirmasi-stok').textContent     = data.stok_saat_ini + ' ' + data.satuan;

                        const fotoEl   = document.getElementById('konfirmasi-foto');
                        const noFotoEl = document.getElementById('konfirmasi-no-foto');
                        if (data.foto) {
                            fotoEl.src            = data.foto;
                            fotoEl.style.display  = 'block';
                            noFotoEl.style.display = 'none';
                        } else {
                            fotoEl.style.display  = 'none';
                            noFotoEl.style.display = 'block';
                        }

                        document.getElementById('btn-lihat-detail').href =
                            detailUrl.replace(':id', data.id);

                        $('#modal-konfirmasi-barang').modal('show');
                    })
                    .catch(err => {
                        alert('Gagal memuat data: ' + err.message);
                    });
            },
            function () {}
        ).catch(err => {
            document.getElementById('qr-scan-status').textContent = 'Kamera tidak bisa diakses: ' + err;
        });
    });

    $('#modal-scanner').on('hidden.bs.modal', function () {
        if (html5QrCode) {
            html5QrCode.stop().catch(() => {});
        }
    });

    document.getElementById('btn-tambah-ke-form').addEventListener('click', function () {
        if (!scannedBarang) return;

        let targetRow = null;
        document.querySelectorAll('.item-row').forEach(row => {
            if (targetRow) return;
            const tipeChecked = row.querySelector('.tipe-radio:checked')?.value;
            const barangId    = $(row).find('.select-barang').val();
            if (tipeChecked === 'per_item' && !barangId) {
                targetRow = row;
            }
        });

        if (!targetRow) {
            document.getElementById('item-container')
                    .insertAdjacentHTML('beforeend', buildItemRow(itemIndex));
            itemIndex++;
            updateRemoveButtons();
            const rows = document.querySelectorAll('.item-row');
            targetRow  = rows[rows.length - 1];
            initSelect2Barang(targetRow);
        }

        const radioPerItem   = targetRow.querySelector('.tipe-radio[value="per_item"]');
        radioPerItem.checked = true;
        targetRow.querySelector('.section-paket-service').style.display = 'none';
        targetRow.querySelector('.section-per-item').style.display      = 'block';

        $(targetRow).find('.select-barang').val(scannedBarang.id).trigger('change');

        targetRow.querySelector('.input-harga-satuan').value       = formatRupiah(scannedBarang.harga_jual);
        targetRow.querySelector('.input-harga-satuan-value').value = scannedBarang.harga_jual;
        targetRow.querySelector('.input-satuan').value             = scannedBarang.satuan;
        targetRow.querySelector('.input-qty').value                = '';

        $('#modal-konfirmasi-barang').modal('hide');
        scannedBarang = null;
    });

    document.querySelector('form').addEventListener('submit', function () {
        hitungTotal();
        document.querySelectorAll('.item-row').forEach(function (row) {
            updateSubtotalFinal(row);
        });
    });
</script>
@stop

@stop