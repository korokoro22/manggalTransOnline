    @extends('adminlte::page')

    @section('title', 'Detail Transaksi Keluar')

    @section('content_header')
        <h1 style="text-transform: uppercase;">Detail Transaksi Keluar</h1>
    @stop

    @section('content')

    <div class="mb-3" style="text-transform: uppercase;">
        <a href="{{ route('barang-keluar.export-pdf-show', $transaksi->id) }}" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('barang-keluar.edit', $transaksi->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>

    {{-- INFO TRANSAKSI --}}
    <x-adminlte-card title="Informasi Transaksi" theme="danger" icon="fas fa-file-invoice" style="text-transform: uppercase;">
        <div class="row">

            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Bus</strong></td>
                        <td>: {{ $transaksi->bus->nama_bus ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Plat Nomor</strong></td>
                        <td>: {{ $transaksi->bus->plat_nomor ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Rute Trayek</strong></td>
                        <td>: {{ $transaksi->bus->rute_trayek ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Driver</strong></td>
                        <td>: {{ $transaksi->bus->nama_driver ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal</strong></td>
                        <td>: {{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Transaksi</strong></td>
                        <td>: <strong class="text-danger">Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>

        </div>
    </x-adminlte-card>

    {{-- DETAIL ITEM --}}
    <x-adminlte-card title="Detail Item Transaksi" theme="danger" icon="fas fa-boxes" style="text-transform: uppercase;">

    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th width="5%">No</th>
                <th width="9%">Tanggal Keluar</th>
                <th width="9%">Tanggal Masuk</th>
                <th>Tipe</th>
                <th width="8%">Foto</th>
                <th>Nama Item</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($transaksi->details as $detail)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                <td class="text-center">
                    @if($detail->tanggal_masuk_terakhir)
                        {{ \Carbon\Carbon::parse($detail->tanggal_masuk_terakhir)->format('d-m-Y H:i') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    @switch($detail->tipe)
                        @case('paket_service')
                            <span class="badge badge-primary">Paket Service</span>
                            @break
                        @case('nota_jalan')
                            <span class="badge badge-info">Nota Jalan</span>
                            @break
                        @case('biaya_pengerjaan')
                            <span class="badge badge-warning">Biaya Pengerjaan</span>
                            @break
                        @default
                            <span class="badge badge-secondary">Per Item</span>
                    @endswitch
                </td>
                <td class="text-center">
                    @if($detail->tipe === 'per_item' && $detail->barang && $detail->barang->foto)
                        <img src="{{ asset('storage/' . $detail->barang->foto) }}"
                            width="50"
                            style="border-radius:5px; object-fit:cover; height:50px;"
                            onclick="showPreview(event, '{{ asset('storage/' . $detail->barang->foto) }}')">
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    {{ $detail->nama_item }}
                    @if($detail->tipe === 'paket_service' && $detail->paketService)
                        <br>
                        <small class="text-muted">
                            Isi paket:
                            @foreach($detail->paketService->paketServiceItem as $psi)
                                {{ $psi->barang->nama_barang ?? '-' }} ({{ $psi->qty }})
                                @if(!$loop->last), @endif
                            @endforeach
                        </small>
                    @elseif($detail->tipe === 'biaya_pengerjaan' && $detail->keterangan)
                        <br>
                        <small class="text-muted">{{ $detail->keterangan }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $detail->qty }}</td>
                <td class="text-center">{{ $detail->satuan ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center text-muted">Tidak ada item</td>
            </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="9" class="text-right"><strong>Total Transaksi</strong></td>
                <td class="text-right">
                    <strong class="text-danger">Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

        <div class="mt-3">
            <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

    </x-adminlte-card>

    {{-- Overlay Modal untuk Preview Gambar --}}
    <div id="image-preview-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; justify-content: center; align-items: center; flex-direction: column;" onclick="if(event.target.id === 'image-preview-overlay') closePreview()">
        <span onclick="closePreview()" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; user-select: none;">&times;</span>
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