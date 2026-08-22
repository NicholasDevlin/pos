{{-- blade-formatter-disable --}}
@php
    $metadata = [
        'title' => 'Produk',
        'breadcrumb' => [['link' => '#', 'menu' => 'Master Data']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <a class="btn btn-dark" type="button" href="{{ route('products.create') }}">Tambah Data</a>
                    </div>
                    <div id="products"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hot = new HandsontableWrapper({
            tableId: 'products',
        }, {
            columns: [
                { data: 'actions', renderer: 'html', className: 'htActions htMiddle' },
                { data: 'category_name', title: 'Kategori' },
                { data: 'code', title: 'Kode' },
                { data: 'name', title: 'Nama' },
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
