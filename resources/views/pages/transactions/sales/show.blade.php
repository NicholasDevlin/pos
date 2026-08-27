@php
    $metadata = [
        'title' => 'Lihat Penjualan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Transaksi'], ['link' => route('sales.index'), 'menu' => 'Penjualan']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Info Penjualan</div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">No. Transaksi</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->series_number }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Customer</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->customer_name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Alamat Customer</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->customer_address }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Diskon Keseluruhan (%)</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->discount_percentage ? $sale->discount_percentage . ' %' : '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Diskon Keseluruhan (Rp)</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->discount_nominal ? 'Rp ' . decimal_number_format($sale->discount_nominal) : '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Pajak</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->tax_percentage ? $sale->tax_percentage . ' %' : '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Keterangan</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->notes ?: '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Status</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{!! $sale->statusLabel() !!}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Total Akhir (Grand Total)</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">Rp {{ decimal_number_format($sale->grand_total) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Item Penjualan</div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No.</th>
                                    <th class="text-center">Produk</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Qty.</th>
                                    <th class="text-center">Diskon %</th>
                                    <th class="text-center">Diskon Nominal</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->saleItems as $item)
                                    @php
                                        $itemSubtotal = $item->price * $item->quantity;
                                        $itemDiscountPct = $itemSubtotal * (($item->discount_percentage ?? 0) / 100);
                                        $itemDiscountNom = ($item->discount_nominal ?? 0);
                                        $itemTotal = $itemSubtotal - $itemDiscountPct - $itemDiscountNom;
                                        if ($itemTotal < 0) {
                                            $itemTotal = 0;
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td class="text-right">Rp {{ decimal_number_format($item->price) }}</td>
                                        <td class="text-right">{{ decimal_number_format($item->quantity) }}</td>
                                        <td class="text-right">{{ $item->discount_percentage ? $item->discount_percentage . '%' : '-' }}</td>
                                        <td class="text-right">{{ $item->discount_nominal ? 'Rp ' . decimal_number_format($item->discount_nominal) : '-' }}</td>
                                        <td class="text-right">Rp {{ decimal_number_format($itemTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">Tidak ada data.</td>
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
