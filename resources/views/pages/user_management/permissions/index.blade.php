@php
    $metadata = [
        'title' => 'Hak Akses',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna'], ['link' => '#', 'menu' => 'Akses Pengguna']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <a class="btn btn-dark" data-remote="true" type="button" href="{{ route('permissions.create') }}">Tambah Data</a>
                    </div>
                    <div id="permissions"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hot = new HandsontableWrapper({
            tableId: 'permissions',
        }, {
            columns: [
                { data: 'actions', renderer: 'html', className: 'htActions htMiddle' },
                { data: 'id', title: 'ID' },
                { data: 'name', title: 'Nama' },
                { data: 'notes', title: 'Keterangan' },
                { data: 'created_at_frmt', title: 'Waktu Pembuatan', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
                { data: 'updated_at_frmt', title: 'Waktu Pembaruan', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
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
