@php
    $metadata = [
        'title' => 'Log Aktivitas',
        'breadcrumb' => [['link' => '#', 'menu' => 'Lainnya']],
    ];
@endphp

@extends('layouts.app')

@push('styles')
    <style>
        .table-logs {
            margin: .25rem .125rem;
        }

        .table-logs thead,
        .table-logs th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .table-logs th,
        .table-logs td {
            padding: .125rem .25rem !important;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div id="activity_logs"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hot = new HandsontableWrapper('activity_logs', {
            columns: [
                { data: 'created_at_frmt', title: 'Waktu Transaksi', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
                { data: 'subject_type_frmt', title: 'Modul' },
                { data: 'subject_id', title: 'ID Modul' },
                { data: 'username', title: 'Penanggung Jawab' },
                { data: 'event_frmt', title: 'Aksi', renderer: 'html' },
                { data: 'changes_frmt', title: 'Pengubahan', renderer: 'html', multiColumnSorting: { headerAction: false } },
            ],
            afterGetColHeader: function(col, th) {
                if (col === 5) {
                    const button = th.children[0].children[0];
                    button.parentNode.removeChild(button);
                }
            },
        }).build();

        axios.get(window.location.href)
            .then(({ data }) => hot.loadData(data))
            .catch(() => toastr['error']('Data tidak berhasil ditampilkan!'));
    </script>
@endpush
