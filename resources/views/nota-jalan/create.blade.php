@extends('adminlte::page')

@section('title', 'Tambah Nota Jalan')

@section('content_header')
    <h1 style="text-transform: uppercase;">Tambah Nota Jalan</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-ban"></i> Validasi Gagal!</h5>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('nota-jalan.store') }}" method="POST" enctype="multipart/form-data" style="text-transform: uppercase;">
@csrf

{{-- HEADER NOTA --}}
<x-adminlte-card title="Informasi Nota Jalan" theme="warning" icon="fas fa-route">

    <div class="row">

        <div class="col-md-6">
            <div class="form-group">
                <label>Bus</label>
                <select name="bus_id"
                        class="form-control @error('bus_id') is-invalid @enderror" style="text-transform: uppercase;"
                        required>
                    <option value="">-- Pilih Bus --</option>
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}" {{ old('bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->nama_bus }} ({{ $bus->plat_nomor }})
                        </option>
                    @endforeach
                </select>
                @error('bus_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="datetime-local"
                       name="tanggal"
                       class="form-control @error('tanggal') is-invalid @enderror"
                       style="text-transform: uppercase;"
                       value="{{ old('tanggal') }}"
                       required>
                @error('tanggal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-6">
            <div class="form-group">
                <label>No. Invoice</label>
                <input type="text"
                       name="no_invoice"
                       class="form-control @error('no_invoice') is-invalid @enderror"
                       placeholder="Masukkan nomor invoice"
                       style="text-transform: uppercase;"
                       value="{{ old('no_invoice') }}"
                       required>
                @error('no_invoice')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Supplier</label>
                <input type="text"
                       name="supplier"
                       style="text-transform: uppercase;"
                       class="form-control @error('supplier') is-invalid @enderror"
                       placeholder="Masukkan nama supplier/toko"
                       value="{{ old('supplier') }}"
                       required>
                @error('supplier')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-6">
            <div class="form-group">
                <label>Bukti Nota</label>
                <input type="file"
                       name="bukti_nota"
                       class="form-control @error('bukti_nota') is-invalid @enderror"
                       style="text-transform: uppercase;"
                       accept="image/*">
                @error('bukti_nota')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>

</x-adminlte-card>

{{-- ITEM BARANG --}}
<x-adminlte-card title="Item Dibeli" theme="warning" icon="fas fa-shopping-basket" style="text-transform: uppercase;">

    <small class="text-muted">Tambahkan item yang dibeli di perjalanan</small>

    <div class="mt-3" id="item-container">

        <div class="card card-outline card-secondary mb-3 item-row">
            <div class="card-header">
                <h6 class="card-title mb-0">Item #1</h6>
            </div>
            <div class="card-body">

                {{-- Tipe Item --}}
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
                                value="biaya_pengerjaan">
                            <label class="form-check-label">Biaya Pengerjaan</label>
                        </div>
                    </div>
                </div>

                {{-- Section Per Item --}}
                <div class="section-per-item">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Item</label>
                                <input type="text"
                                    name="items[0][nama_item]"
                                    class="form-control input-nama-item"
                                    placeholder="Masukkan nama item" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number"
                                    name="items[0][qty]"
                                    class="form-control input-qty"
                                    placeholder="Contoh: 2"
                                    style="text-transform: uppercase;"
                                    min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Satuan</label>
                                <input type="text"
                                    name="items[0][satuan]"
                                    class="form-control input-satuan"
                                    style="text-transform: uppercase;"
                                    placeholder="Pcs, Botol, Liter">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Satuan</label>
                                <input type="number"
                                    name="items[0][harga_satuan]"
                                    class="form-control input-harga-satuan"
                                    placeholder="Harga per satuan"
                                    style="text-transform: uppercase;"
                                    min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Subtotal <small class="text-muted">(otomatis, bisa diedit)</small></label>
                                <input type="number"
                                    name="items[0][subtotal]"
                                    class="form-control input-subtotal"
                                    placeholder="Subtotal"
                                    style="text-transform: uppercase;"
                                    min="0">
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
                                    placeholder="Contoh: Ongkos bongkar muat, servis darurat di jalan"
                                    disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Biaya Pengerjaan</label>
                                <input type="number"
                                    name="items[0][subtotal]"
                                    class="form-control input-biaya-pengerjaan"
                                    placeholder="Masukkan biaya"
                                    style="text-transform: uppercase;"
                                    min="0"
                                    disabled>
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

    <button type="button" class="btn btn-success btn-sm" id="btn-add-item">
        <i class="fas fa-plus"></i> Tambah Item
    </button>

    <hr>

    <div class="row">
        <div class="col-md-4 offset-md-8">
            <div class="form-group">
                <label><strong>Total Keseluruhan</strong></label>
                <input type="number"
                       name="total"
                       class="form-control"
                       placeholder="Total keseluruhan"
                       style="text-transform: uppercase;"
                       min="0"
                       required>
            </div>
        </div>
    </div>

</x-adminlte-card>

<div class="mb-3">
    <a href="{{ route('nota-jalan.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i>
        Simpan
    </button>
</div>

</form>

@section('js')
<script>
    let itemIndex = 1;

    function buildItemRow(index) {
        return `
        <div class="card card-outline card-secondary mb-3 item-row">
            <div class="card-header">
                <h6 class="card-title mb-0">Item #${index + 1}</h6>
            </div>
            <div class="card-body" style="text-transform: uppercase;">

                <div class="form-group">
                    <label>Tipe Item</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="per_item" checked>
                            <label class="form-check-label">Per Item</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input tipe-radio" type="radio" name="items[${index}][tipe]" value="biaya_pengerjaan">
                            <label class="form-check-label">Biaya Pengerjaan</label>
                        </div>
                    </div>
                </div>

                <div class="section-per-item">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Item</label>
                                <input type="text" name="items[${index}][nama_item]" class="form-control input-nama-item" placeholder="Masukkan nama item" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" name="items[${index}][qty]" class="form-control input-qty" placeholder="Contoh: 2" min="1" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Satuan</label>
                                <input type="text" name="items[${index}][satuan]" class="form-control input-satuan" placeholder="Pcs, Botol, Liter" style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Satuan</label>
                                <input type="number" name="items[${index}][harga_satuan]" class="form-control input-harga-satuan" placeholder="Harga per satuan" min="0" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Subtotal <small class="text-muted">(otomatis, bisa diedit)</small></label>
                                <input type="number" name="items[${index}][subtotal]" class="form-control input-subtotal" placeholder="Subtotal" min="0" style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-biaya-pengerjaan" style="display:none">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Keterangan Pengerjaan</label>
                                <input type="text" name="items[${index}][keterangan]" class="form-control input-keterangan-pengerjaan" placeholder="Contoh: Ongkos bongkar muat, servis darurat di jalan" style="text-transform: uppercase;" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Biaya Pengerjaan</label>
                                <input type="number" name="items[${index}][subtotal]" class="form-control input-biaya-pengerjaan" placeholder="Masukkan biaya" min="0" style="text-transform: uppercase;" required>
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

    document.getElementById('btn-add-item').addEventListener('click', function () {
        const container = document.getElementById('item-container');
        container.insertAdjacentHTML('beforeend', buildItemRow(itemIndex));
        itemIndex++;
        updateRemoveButtons();
    });

    document.getElementById('item-container').addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-item')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row) => {
            const btn = row.querySelector('.btn-remove-item');
            btn.disabled = rows.length === 1;
        });
    }

    function hitungSubtotalItem(row) {
        const qtyInput   = row.querySelector('input[name$="[qty]"]');
        const hargaInput = row.querySelector('input[name$="[harga_satuan]"]');
        const subtotalInput = row.querySelector('input[name$="[subtotal]"]');

        const qty   = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;

        subtotalInput.value = qty * harga;
    }

    function hitungTotalKeseluruhan() {
        let total = 0;
        document.querySelectorAll('.item-row input[name$="[subtotal]"]').forEach(function (input) {
            total += parseFloat(input.value) || 0;
        });
        document.querySelector('input[name="total"]').value = total;
    }

    // ========== Event: ganti tipe item ==========
    // document.getElementById('item-container').addEventListener('change', function (e) {
    //     if (e.target.classList.contains('tipe-radio')) {
    //         const row = e.target.closest('.item-row');
    //         const sectionPerItem = row.querySelector('.section-per-item');
    //         const sectionBiaya   = row.querySelector('.section-biaya-pengerjaan');

    //         if (e.target.value === 'biaya_pengerjaan') {
    //             sectionPerItem.style.display = 'none';
    //             sectionBiaya.style.display   = 'block';
    //         } else {
    //             sectionPerItem.style.display = 'block';
    //             sectionBiaya.style.display   = 'none';
    //         }

    //         // reset subtotal saat ganti tipe
    //         row.querySelector('.input-subtotal').value = 0;
    //         hitungTotalKeseluruhan();
    //     }
    // });

    // ========== Event: ganti tipe item ==========
    document.getElementById('item-container').addEventListener('change', function (e) {
        if (e.target.classList.contains('tipe-radio')) {
            const row = e.target.closest('.item-row');
            const sectionPerItem = row.querySelector('.section-per-item');
            const sectionBiaya   = row.querySelector('.section-biaya-pengerjaan');

            if (e.target.value === 'biaya_pengerjaan') {
                sectionPerItem.style.display = 'none';
                // Matikan input per_item agar tidak dikirim
                sectionPerItem.querySelectorAll('input').forEach(i => i.disabled = true);
                
                sectionBiaya.style.display   = 'block';
                // Nyalakan input biaya_pengerjaan agar dikirim
                sectionBiaya.querySelectorAll('input').forEach(i => i.disabled = false);
            } else {
                sectionPerItem.style.display = 'block';
                // Nyalakan input per_item
                sectionPerItem.querySelectorAll('input').forEach(i => i.disabled = false);
                
                sectionBiaya.style.display   = 'none';
                // Matikan input biaya_pengerjaan agar tidak dikirim
                sectionBiaya.querySelectorAll('input').forEach(i => i.disabled = true);
            }

            // reset subtotal saat ganti tipe
            const subtotalInput = row.querySelector('.input-subtotal');
            if(subtotalInput) subtotalInput.value = 0;
            
            const biayaInput = row.querySelector('.input-biaya-pengerjaan');
            if(biayaInput) biayaInput.value = '';

            hitungTotalKeseluruhan();
        }
    });


    // ========== Event: hitung subtotal ==========
    document.getElementById('item-container').addEventListener('input', function (e) {
        // Per Item: qty x harga
        if (e.target.matches('input[name$="[qty]"]') || e.target.matches('input[name$="[harga_satuan]"]')) {
            const row = e.target.closest('.item-row');
            hitungSubtotalItem(row);
        }

        // Semua tipe (Per Item hasil hitung, atau Biaya Pengerjaan input manual) ikut dihitung ke total
        if (e.target.matches('input[name$="[subtotal]"]') ||
            e.target.matches('input[name$="[qty]"]') ||
            e.target.matches('input[name$="[harga_satuan]"]')) {
            hitungTotalKeseluruhan();
        }
    });

</script>
@stop

@stop