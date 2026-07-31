@extends('adminlte::page')

@section('title', 'Detail Barang Masuk')

@section('content_header')
    <h1 style="text-transform: uppercase;">Detail Barang Masuk</h1>
@stop

@section('content')

<div class="mb-3" style="text-transform: uppercase;">
    <a href="{{ route('barang-masuk.export-pdf-show', $barangMasuk->id) }}" class="btn btn-success">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
    <a href="{{ route('barang-masuk.edit', $barangMasuk->id) }}" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
    </a>
</div>

{{-- INFORMASI NOTA --}}
<x-adminlte-card title="Informasi Nota" theme="success" icon="fas fa-file-invoice" style="text-transform: uppercase;">
    <div class="row">

        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <td width="40%"><strong>No. Invoice</strong></td>
                    <td>: {{ $barangMasuk->no_invoice }}</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Masuk</strong></td>
                    <td>: {{ \Carbon\Carbon::parse($barangMasuk->tanggal_masuk)->format('d-m-Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Supplier</strong></td>
                    <td>: {{ $barangMasuk->supplier }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <td width="40%"><strong>Penerima</strong></td>
                    <td>: {{ $barangMasuk->penerima }}</td>
                </tr>
                <tr>
                    <th>Kategori Nota</th>
                    <td>
                        @if($barangMasuk->kategori_nota === 'nota_jalan')
                            <span class="badge badge-warning">Nota Jalan</span>
                        @else
                            <span class="badge badge-primary">Nota Bengkel</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Bukti Nota</strong></td>
                    <td>
                        @if($barangMasuk->bukti_nota)
                            :
                            <img src="{{ asset('storage/' . $barangMasuk->bukti_nota) }}"
                                 style="max-height:80px; border-radius:5px; cursor:pointer"
                                 data-toggle="modal"
                                 data-target="#modalNota">
                        @else
                            : <span class="text-muted">Tidak ada</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

    </div>
</x-adminlte-card>


{{-- DETAIL ITEM --}}
<x-adminlte-card title="Detail Item Barang" theme="success" icon="fas fa-boxes" style="text-transform: uppercase;">

    <table class="table table-bordered table-striped">

        <thead class="text-center">
            <tr>
                <th width="5%">No</th>
                <th>Foto</th>
                <th>Kategori</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Total (Pcs)</th>
                <th>Harga Jual</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($barangMasuk->details as $detail)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">
                    @if($detail->foto)
                        <img src="{{ asset('storage/' . $detail->foto) }}"
                             style="width:60px; height:60px; object-fit:cover; border-radius:5px"
                             onclick="showPreview(event, '{{ asset('storage/' . $detail->foto) }}')">
                             >
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    @php
                        $badge = match($detail->kategori) {
                            'oli_mesin'    => 'warning',
                            'filter_solar' => 'info',
                            default        => 'secondary',
                        };
                        $label = match($detail->kategori) {
                            'oli_mesin'    => 'Oli Mesin',
                            'filter_solar' => 'Filter Solar',
                            default        => 'Item Bebas',
                        };
                    @endphp
                    <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                </td>
                <td>{{ $detail->nama_barang }}</td>
                <td class="text-center">{{ $detail->qty }}</td>
                <td class="text-center">{{ $detail->satuan }}</td>
                <td class="text-center">{{ $detail->qty_satuan }} Pcs</td>
                <td class="text-right">Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">Tidak ada item barang</td>
            </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="8" class="text-right"><strong>Total Keseluruhan</strong></td>
                <td class="text-right">
                    <strong>Rp {{ number_format($barangMasuk->details->sum('subtotal'), 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>

    </table>

    <div class="mt-3">
        <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

</x-adminlte-card>


{{-- MODAL BUKTI NOTA --}}
@if($barangMasuk->bukti_nota)
<div class="modal fade" id="modalNota" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Nota — {{ $barangMasuk->no_invoice }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/' . $barangMasuk->bukti_nota) }}"
                     class="img-fluid"
                     style="border-radius:5px">
            </div>
        </div>
    </div>
</div>
@endif

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