{{-- blade-formatter-disable --}}
@php
    $metadata = [
        'title' => 'Penagihan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Transaksi']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div id="invoices"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hot = new HandsontableWrapper({
            tableId: 'invoices',
        }, {
            columns: [
                { data: 'actions', renderer: 'html', className: 'htActions htMiddle' },
                { data: 'sale_series_number', title: 'No. Penjualan' },
                { data: 'series_number', title: 'No. Transaksi' },
                { data: 'customer_name', title: 'Nama Customer' },
                { data: 'status', title: 'Status', renderer: 'html' },
                { data: 'created_at', title: 'Waktu Pembuatan', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
                { data: 'updated_at', title: 'Waktu Pembaruan', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
            ],
            afterGetColHeader: function(col, th) {
                if (col === 0) {
                    th.innerHTML = '';
                }
            },
        }).build();

        axios.get(window.location.href)
            .then(({ data }) => hot.loadData(data))
            .catch(() => toastr['error']('Data tidak berhasil ditampilkan!'));
    </script>
@endpush
