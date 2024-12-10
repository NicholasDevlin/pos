{{-- blade-formatter-disable --}}
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
        @if (request()->input('all') !== 'true')
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body position-relative">
                        {{ html()->form()->id('show-grid')->attributes(['autocomplete' => 'off'])->open() }}

                        <div class="form-group mb-4 col-lg-5">
                            @php
                                $label = 'Tanggal Transaksi';
                                $name = 'date';
                                $radioName = 'date_option';
                                $fixedDateName = 'fixed_date';
                                $relativeDateName = 'relative_date';
                            @endphp
                            {{ html()->label($label, $name) }}
                            <span class="text-danger">*</span>

                            <table class="table table-sm table-borderless ml-3">
                                <tbody>
                                    <tr>
                                        <td style="width: 150px;">
                                            {{ html()->radio($radioName, null, 'fixed')->class(['form-check-input']) }}
                                            {{ html()->label('Mulai dari ...<br>Sampai saat ini', $radioName . '_fixed') }}
                                        </td>
                                        <td>
                                            {{ html()->select($fixedDateName, $fixedDateOptions)->placeholder('')->class('form-control' . ($errors->has($fixedDateName) ? ' is-invalid' : ''))->disabled() }}

                                            <span class="{{ $fixedDateName }}--error" role="alert"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {{ html()->radio($radioName, null, 'relative')->class(['form-check-input']) }}
                                            {{ html()->label('Range Tanggal', $radioName . '_relative') }}

                                            <span class="{{ $radioName }}--error" role="alert"></span>
                                        </td>
                                        <td>
                                            {{ html()->text($relativeDateName)->class('form-control' . ($errors->has($relativeDateName) ? ' is-invalid' : ''))->disabled() }}

                                            <span class="{{ $relativeDateName }}--error" role="alert"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-lg-4">
                            {{ html()->submit('Tampil Data')->class('btn btn-primary loader-button')->prependChild('<span class="spinner-border spinner-border-sm mr-2 d-none" role="status" aria-hidden="true"></span>') }}
                        </div>

                        @role('super-admin')
                            <a class='btn btn-sm btn-light position-absolute' href="{{ route('logs', ['all' => 'true']) }}" title="Show All" style="top: 20px; right: 20px;">Tampil Semua</a>
                        @endrole

                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-12 d-none" id="activity-logs_container">
            <div class="card">
                <div class="card-body">
                    <div id="activity-logs"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hot = new HandsontableWrapper({
            tableId: 'activity-logs',
            isExportEnabled: false,
        }, {
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

        @if (request()->input('all') !== 'true')
            $(function() {
                $('input[type=radio][name=date_option]').change(function() {
                    const fixedDateSelect = $('#fixed_date');
                    const relativeDatePicker = $('#relative_date');

                    if (this.value === 'fixed') {
                        relativeDatePicker.val(null);
                        relativeDatePicker.prop('disabled', true);
                        fixedDateSelect.prop('disabled', false);
                    } else if (this.value === 'relative') {
                        fixedDateSelect.val(null);
                        fixedDateSelect.prop('disabled', true);
                        relativeDatePicker.prop('disabled', false);
                    }
                });

                $('[name="relative_date"]')
                    .daterangepicker({
                        locale: {
                            format: 'DD-MM-YYYY',
                        },
                        autoUpdateInput: false,
                    })
                    .on('apply.daterangepicker', function(ev, picker) {
                        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
                    })
                    .on('cancel.daterangepicker', function() {
                        $(this).val('');
                    });

                $('form[id="show-grid"]').submit(function(e) {
                    e.preventDefault();

                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').html('');

                    $(this).find('.loader-button').attr('disabled', 'disabled');
                    $(this).find('.loader-button span').removeClass('d-none');

                    $('#activity-logs_container').addClass('d-none');

                    const formData = $('[name!=_token]', this).serializeArray();
                    const separator = window.location.search ? '&' : '?';

                    axios.get(`${window.location.href}${separator}${formData.map((f) => `${f.name}=${f.value}`).join('&')}`)
                        .then(({ data }) => {
                            $('#activity-logs_container').removeClass('d-none');

                            hot.loadData(data);
                        })
                        .catch((e) => {
                            if (e?.response?.status === 422) {
                                const { errors } = e.response.data;

                                Object.keys(errors).forEach((key) => {
                                    const message = errors[key][0];

                                    $(`[name="${key}"]`)
                                        .addClass('is-invalid')
                                        .parent('.multiselect-native-select')?.append(`<span class="animated fadeIn ${key}--error" role="alert"></span>`);
                                    $(`.${key}--error`).addClass('invalid-feedback').text(message);
                                });
                            } else {
                                toastr['error']('Data tidak berhasil ditampilkan!');
                            }
                        })
                        .finally(() => {
                            $(this).find('.loader-button').removeAttr('disabled');
                            $(this).find('.loader-button span').addClass('d-none');
                        });
                });
            });
        @else
            $('#activity-logs_container').removeClass('d-none');

            axios.get(window.location.href)
                .then(({ data }) => hot.loadData(data))
                .catch(() => toastr['error']('Data tidak berhasil ditampilkan!'));
        @endif
    </script>
@endpush
