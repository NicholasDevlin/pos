@php
    $metadata = [
        'title' => 'Inisialisasi Data',
        'breadcrumb' => [['link' => '#', 'menu' => 'Lainnya']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Download Template</div>

                    {{ html()->form('POST', route('data-initiation.download-template'))->attributes(['autocomplete' => 'off'])->open() }}

                    <div class="form-group mb-4 col-6">
                        @php
                            $label = 'Model';
                            $name = 'model';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->select($name, $models, old($name) ?? null)->placeholder('')->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    {{ html()->submit('Download')->class('btn btn-primary') }}

                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Impor Data</div>

                    @if ($failures = Session::get('failures'))
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($failures as $failure)
                                    <li>Baris {{ $loop->iteration }}: {{ $failure->getMessage() }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ html()->form('POST', route('data-initiation.import'))->attributes(['autocomplete' => 'off'])->acceptsFiles()->open() }}

                    <div class="form-group mb-4 col-6">
                        @php
                            $label = 'Model';
                            $name = 'model';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->select($name, $models, old($name) ?? null)->placeholder('')->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4 col-6">
                        @php
                            $label = 'File';
                            $name = 'file';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        <div class="custom-file">
                            {{ html()->file($name)->class('custom-file-input' . ($errors->has($name) ? ' is-invalid' : '')) }}
                            <label class="custom-file-label" for="customFile">Choose file</label>

                            @error($name)
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    {{ html()->submit('Impor')->class('btn btn-primary') }}

                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('.custom-file-input').on('change', function() {
                const fileName = $('#{{ $name }}')[0].files[0].name;
                $('.custom-file-label').text(fileName);
            });
        });
    </script>
@endpush
