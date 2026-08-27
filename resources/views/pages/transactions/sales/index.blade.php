{{-- blade-formatter-disable --}}
@php
    $metadata = [
        'title' => 'Penjualan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Transaksi']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <iframe class="d-none" id="print-iframe" src="about:blank" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <div class="d-flex justify-content-between">
                            <a class="btn btn-dark" type="button" href="{{ route('sales.create') }}">Tambah Data</a>

                            <a class="btn btn-secondary text-white" data-remote="true" type="button" href="{{ route('sales.edit-company-profile') }}">Edit Profil Perusahaan</a>
                        </div>
                    </div>
                    <div id="sales"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hot = new HandsontableWrapper({
            tableId: 'sales',
        }, {
            columns: [
                { data: 'actions', renderer: 'html', className: 'htActions htMiddle' },
                { data: 'series_number', title: 'No. Transaksi' },
                { data: 'customer_name', title: 'Nama Customer' },
                { data: 'customer_address', title: 'Alamat Customer' },
                { data: 'notes', title: 'Keterangan' },
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
