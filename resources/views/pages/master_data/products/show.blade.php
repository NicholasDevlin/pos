@php
    $metadata = [
        'title' => 'Lihat Produk',
        'breadcrumb' => [['link' => '#', 'menu' => 'Master Data'], ['link' => route('products.index'), 'menu' => 'Produk']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Info Produk</div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Kategori Produk</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $product->productCategory->name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Kode</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $product->code }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Nama</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $product->name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Keterangan</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $product->notes ?: '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Status</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{!! $product->statusLabel() !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Info Satuan & Harga</div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Bawaan</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">SKU</th>
                                    <th class="text-center">Konversi Satuan</th>
                                    <th class="text-center">Harga Modal</th>
                                    <th class="text-center">Harga Dasar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->productUoms as $productUom)
                                    @php
                                        $backgroundColor = $loop->odd ? '#ffffff' : '#f9f9f9';
                                    @endphp
                                    <tr style="background-color: {{ $backgroundColor }}">
                                        <td class="text-center" rowspan="2">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $productUom->is_default ? '✅' : '❌' }}</td>
                                        <td class="text-center">{{ $productUom->unitOfMeasure->name }}</td>
                                        <td>{{ $productUom->sku }}</td>
                                        <td class="text-right">{{ decimal_number_format($productUom->conversion_factor) }}</td>
                                        <td class="text-right"><span style="float: left;">Rp</span>{{ decimal_number_format($productUom->cost_price) }}</td>
                                        <td class="text-right"><span style="float: left;">Rp</span>{{ decimal_number_format($productUom->base_price) }}</td>
                                    </tr>
                                    <tr style="background-color: {{ $backgroundColor }}">
                                        <td colspan="6">
                                            <div class="row">
                                                @php $productTierPrices = $productUom->tierPrices->keyBy('tier'); @endphp
                                                @foreach($tierList as $tierKey => $tierName)
                                                    <div class="col-4">
                                                        Harga {{ $tierName }}: <strong>Rp {{ decimal_number_format($productTierPrices[$tierKey]->price ?? null) }}</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
