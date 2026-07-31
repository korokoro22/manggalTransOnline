{{-- view edit.blade.php --}}

@extends('adminlte::page')

@section('title', 'Edit Transaksi Keluar')

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
    <h1 style="text-transform: uppercase;">Edit Transaksi Keluar</h1>
@stop

@section('content')

@if(session('error'))
    <div class="alert alert-danger" style="text-transform: uppercase;">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

<x-adminlte-card title="Form Edit Transaksi Keluar" theme="danger" icon="fas fa-edit" style="text-transform: uppercase;">

    <form action="{{ route('barang-keluar.update', $transaksi->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bus</label>
                    <select name="bus_id" id="bus-select" class="form-control" required style="text-transform: uppercase;">
                        <option value="">-- Pilih Bus --</option>
                        @foreach($busList as $bus)
                            <option value="{{ $bus->id }}"
                                {{ $transaksi->bus_id == $bus->id ? 'selected' : '' }}>
                                {{ $bus->nama_bus }} - {{ $bus->plat_nomor }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="datetime-local"
                           name="tanggal"
                           class="form-control"
                           value="{{ $transaksi->tanggal }}"
                           required>
                </div>
            </div>
        </div>

        <hr>

        <h5><i class="fas fa-list"></i> Item Transaksi</h5>
        <small class="text-muted">Edit item transaksi</small>

        <div class="mt-3" id="item-container">

            @foreach($transaksi->details as $index => $detail)
            <div class="card card-outline card-secondary mb-3 item-row">
                <div class="card-body">

                    {{-- Tipe --}}
                    {{-- <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tipe Item</label>
                                
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input tipe-radio"
                                               type="radio"
                                               name="items[{{ $index }}][tipe]"
                                               value="per_item"
                                               {{ $detail->tipe === 'per_item' ? 'checked' : '' }}>
                                        <label class="form-check-label">Per Item</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input tipe-radio"
                                               type="radio"
                                               name="items[{{ $index }}][tipe]"
                                               value="paket_service"
                                               {{ $detail->tipe === 'paket_service' ? 'checked' : '' }}>
                                        <label class="form-check-label">Paket Service</label>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    {{-- Tipe (fixed, tidak bisa diubah untuk item yang sudah ada) --}}
                    <input type="hidden" name="items[{{ $index }}][tipe]" value="{{ $detail->tipe }}">
                    <div class="mb-2">
                        <label class="d-block mb-1">Tipe Item</label>
                        <span class="badge badge-{{ $detail->tipe === 'paket_service' ? 'primary' : 'secondary' }} p-2" style="text-transform: uppercase;">
                            <i class="fas {{ $detail->tipe === 'paket_service' ? 'fa-tools' : 'fa-box' }}"></i>
                            {{ $detail->tipe === 'paket_service' ? 'Paket Service' : 'Per Item' }}
                        </span>
                        <small class="text-muted d-block mt-1">Tipe item tidak bisa diubah setelah dibuat. Hapus item ini dan tambahkan item baru jika ingin ganti tipe.</small>
                    </div>

                    {{-- Section Paket Service --}}
                    <div class="section-paket-service" style="{{ $detail->tipe === 'paket_service' ? '' : 'display:none' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paket Service</label>
                                    <select name="items[{{ $index }}][paket_service_id]"
                                            class="form-control select-paket" style="text-transform: uppercase;">
                                        <option value="">-- Pilih Paket Service --</option>
                                        @if($detail->tipe === 'paket_service' && $detail->paketService)
                                            <option value="{{ $detail->paketService->id }}" selected>
                                                {{ $detail->paketService->nama_paket }} — Rp {{ number_format($detail->paketService->harga, 0, ',', '.') }}
                                            </option>
                                        @endif
                                    </select>
                                    <small class="text-muted">Pilih ulang bus untuk memuat paket service lain</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Harga Paket</label>
                                    <input type="number"
                                           class="form-control input-harga-paket"
                                           value="{{ $detail->tipe === 'paket_service' ? $detail->harga_satuan : '' }}"
                                           style="text-transform: uppercase;"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Subtotal</label>
                                    <input type="hidden" name="items[{{ $index }}][subtotal]" class="input-subtotal-value" value="{{ $detail->tipe === 'paket_service' ? $detail->subtotal : '' }}">
                                    <input type="number"
                                           class="form-control input-subtotal"
                                           style="text-transform: uppercase;"
                                           value="{{ $detail->tipe === 'paket_service' ? $detail->subtotal : '' }}"
                                           >
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section Per Item --}}
                    <div class="section-per-item" style="{{ $detail->tipe === 'per_item' ? '' : 'display:none' }}">
                        <div class="form-group">
                            <label>Pilih Barang</label>
                            <select name="items[{{ $index }}][barang_id]" class="form-control select-barang" style="text-transform: uppercase;">
                                <option value="">-- Cari nama barang atau kode barang --</option>
                                @foreach($barangs as $barang)
                                    <option value="{{ $barang['id'] }}" {{ $detail->barang_id == $barang['id'] ? 'selected' : '' }}>
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
                                           name="items[{{ $index }}][satuan]"
                                           class="form-control input-satuan"
                                           value="{{ $detail->satuan }}"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Harga Satuan</label>
                                    <input type="number"
                                           name="items[{{ $index }}][harga_satuan]"
                                           class="form-control input-harga-satuan"
                                           value="{{ $detail->harga_satuan }}"
                                           placeholder="Otomatis terisi"
                                           style="text-transform: uppercase;"
                                           min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Qty</label>
                                    <input type="number"
                                           name="items[{{ $index }}][qty]"
                                           class="form-control input-qty"
                                           value="{{ $detail->qty }}"
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
                                    <input type="hidden" name="items[{{ $index }}][subtotal]" class="input-subtotal-value" value="{{ $detail->tipe === 'per_item' ? $detail->subtotal : '' }}">
                                    <input type="number"
                                           class="form-control input-subtotal"
                                           value="{{ $detail->tipe === 'per_item' ? $detail->subtotal : '' }}"
                                           style="text-transform: uppercase;"
                                           placeholder="Otomatis terisi"
                                           >
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-danger btn-sm btn-remove-item" {{ $transaksi->details->count() === 1 ? 'disabled' : '' }}>
                        <i class="fas fa-trash"></i> Hapus Item
                    </button>

                </div>
            </div>
            @endforeach

        </div>

        <button type="button" class="btn btn-success btn-sm mb-3" id="btn-add-item">
            <i class="fas fa-plus"></i> Tambah Item
        </button>

        <hr>

        <div class="row">
            <div class="col-md-4 offset-md-8">
                <div class="form-group">
                    <label><strong>Total Transaksi</strong></label>
                    <input type="number"
                           id="total-transaksi"
                           name="total_transaksi"
                           class="form-control"
                           value="{{ $transaksi->total_transaksi }}"
                           >
                </div>
            </div>
        </div>

        <a href="{{ route('barang-keluar.show', $transaksi->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> KEMBALI
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> SIMPAN PERUBAHAN
        </button>

    </form>

    {{-- Overlay Modal untuk Preview Gambar --}}
    <div id="image-preview-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; justify-content: center; align-items: center; flex-direction: column;" onclick="if(event.target.id === 'image-preview-overlay') closePreview()">
        <span onclick="closePreview()" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; user-select: none;">&times;</span>
        <img id="image-preview-src" src="" style="max-width: 90%; max-height: 85%; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
    </div>

</x-adminlte-card>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let itemIndex = {{ $transaksi->details->count() }};

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

    const ajaxUrl  = "{{ route('barang-keluar.paket-by-bus', ':busId') }}";
    const barangs  = @json($barangs);
    const currentBusId = {{ $transaksi->bus_id }};

    // ========== Init Select2 Custom ==========
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
            
            // Format text gudang (gudang_utama -> Gudang Utama)
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
        const b        = barangs[parseInt(barangId)] || barangs[barangId];
        const r        = $(this).closest('.item-row')[0];

        if (b) {
            r.querySelector('.input-harga-satuan').value = b.harga_jual;
            r.querySelector('.input-satuan').value       = b.satuan;
        } else {
            r.querySelector('.input-harga-satuan').value = '';
            r.querySelector('.input-satuan').value       = '';
        }

        // Jika bukan data awal yg diload (inputan baru), reset qty dan subtotal
        if ($(this).data('loaded') !== true) {
            r.querySelector('.input-qty').value = '';
            r.querySelector('.section-per-item .input-subtotal').value       = 0;
            r.querySelector('.section-per-item .input-subtotal-value').value = 0;
        } else {
            $(this).data('loaded', false); 
        }
        hitungTotal();
    });
}

    // ========== Eksekusi saat halaman diload ==========
    window.addEventListener('DOMContentLoaded', function () {
        loadPaket(currentBusId, false);

        document.querySelectorAll('.item-row').forEach(row => {
            const select = $(row).find('.select-barang');
            select.data('loaded', true); 
            initSelect2Barang(row);
        });
    });

    function loadPaket(busId, resetDropdown = true) {
        if (!busId) return;

        fetch(ajaxUrl.replace(':busId', busId))
            .then(res => res.json())
            .then(data => {
                paketList = data;
                if (resetDropdown) {
                    document.querySelectorAll('.select-paket').forEach(sel => {
                        const currentVal = sel.value;
                        sel.innerHTML = '<option value="">-- Pilih Paket Service --</option>';
                        data.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value         = p.id;
                            opt.text          = p.nama_paket + ' — Rp ' + Number(p.harga).toLocaleString('id-ID');
                            opt.dataset.harga = p.harga;
                            if (p.id == currentVal) opt.selected = true;
                            sel.appendChild(opt);
                        });
                        sel.disabled = false;
                    });
                }
            });
    }

    // Ganti bus
    document.getElementById('bus-select').addEventListener('change', function () {
        loadPaket(this.value, true);
    });

    // Build item row baru
    function buildItemRow(index) {
        const barangOptions = Object.values(barangs).map(b =>
            `<option value="${b.id}">${b.nama_barang} ${b.kode_barang}</option>`
        ).join('');

        const paketOptions = paketList.length
            ? paketList.map(p => `<option value="${p.id}" data-harga="${p.harga}">${p.nama_paket} — Rp ${Number(p.harga).toLocaleString('id-ID')}</option>`).join('')
            : '';

        return `
        <div class="card card-outline card-secondary mb-3 item-row" style="text-transform: uppercase;">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Tipe Item</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="per_item" checked>
                                    <label class="form-check-label">Per Item</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="paket_service">
                                    <label class="form-check-label">Paket Service</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <input type="text" name="items[${index}][satuan]" class="form-control input-satuan" placeholder="Otomatis terisi" style="text-transform: uppercase;" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Harga Satuan</label>
                                <input type="number" name="items[${index}][harga_satuan]" class="form-control input-harga-satuan" style="text-transform: uppercase;" placeholder="Otomatis terisi" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" name="items[${index}][qty]" class="form-control input-qty" placeholder="Masukkan qty" style="text-transform: uppercase;" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Subtotal</label>
                                <input type="hidden" name="items[${index}][subtotal]" class="input-subtotal-value">
                                <input type="number" class="form-control input-subtotal" placeholder="Otomatis terisi" style="text-transform: uppercase;">
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
                                <input type="number" class="form-control input-harga-paket" placeholder="Otomatis terisi" style="text-transform: uppercase;" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Subtotal</label>
                                <input type="hidden" name="items[${index}][subtotal]" class="input-subtotal-value">
                                <input type="number" class="form-control input-subtotal" placeholder="Otomatis terisi" style="text-transform: uppercase;" readonly>
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

    // Event: change
    document.getElementById('item-container').addEventListener('change', function (e) {
        const row = e.target.closest('.item-row');
        if (!row) return;

        if (e.target.classList.contains('tipe-radio')) {
            const sectionPaket = row.querySelector('.section-paket-service');
            const sectionItem  = row.querySelector('.section-per-item');
            if (e.target.value === 'paket_service') {
                sectionPaket.style.display = 'block';
                sectionItem.style.display  = 'none';
            } else {
                sectionPaket.style.display = 'none';
                sectionItem.style.display  = 'block';
            }
            resetSubtotal(row);
        }

        if (e.target.classList.contains('select-paket')) {
            const harga = e.target.selectedOptions[0]?.dataset.harga || 0;
            row.querySelector('.input-harga-paket').value = harga;
            row.querySelector('.section-paket-service .input-subtotal').value       = harga;
            row.querySelector('.section-paket-service .input-subtotal-value').value = harga;
            hitungTotal();
        }
    });

    // Event: input qty & harga satuan
    document.getElementById('item-container').addEventListener('input', function (e) {
        const row = e.target.closest('.item-row');
        if (!row) return;

        if (e.target.classList.contains('input-qty') || e.target.classList.contains('input-harga-satuan')) {
            const qty      = parseInt(row.querySelector('.input-qty').value) || 0;
            const harga    = parseFloat(row.querySelector('.input-harga-satuan').value) || 0;
            const subtotal = qty * harga;
            row.querySelector('.section-per-item .input-subtotal').value       = subtotal;
            row.querySelector('.section-per-item .input-subtotal-value').value = subtotal;
            hitungTotal();
        }
    });

    // Event: hapus item
    document.getElementById('item-container').addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-item')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
            hitungTotal();
        }
    });

    // Event: tambah item
    document.getElementById('btn-add-item').addEventListener('click', function () {
        document.getElementById('item-container')
                .insertAdjacentHTML('beforeend', buildItemRow(itemIndex));
        itemIndex++;
        updateRemoveButtons();
        const rows = document.querySelectorAll('.item-row');
        initSelect2Barang(rows[rows.length - 1]);
    });

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const tipe = row.querySelector('.tipe-radio:checked')?.value;
            if (tipe === 'paket_service') {
                total += parseFloat(row.querySelector('.section-paket-service .input-subtotal')?.value) || 0;
            } else {
                total += parseFloat(row.querySelector('.section-per-item .input-subtotal')?.value) || 0;
            }
        });
        document.getElementById('total-transaksi').value = total;
    }

    function resetSubtotal(row) {
        row.querySelector('.section-paket-service .input-subtotal').value       = 0;
        row.querySelector('.section-paket-service .input-subtotal-value').value = 0;
        row.querySelector('.section-per-item .input-subtotal').value            = 0;
        row.querySelector('.section-per-item .input-subtotal-value').value      = 0;
        hitungTotal();
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            row.querySelector('.btn-remove-item').disabled = rows.length === 1;
        });
    }
</script>
@stop
@stop