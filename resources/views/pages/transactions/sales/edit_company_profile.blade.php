@php
    $metadata = [
        'title' => 'Profil Perusahaan',
    ];
@endphp

@extends('layouts.modal')

@section('start')
    {{ html()->form('PUT', route('sales.update-company-profile'))->id('edit-data')->attributes(['data-remote' => 'true', 'autocomplete' => 'off'])->open() }}
    @php html()->model($option); @endphp
@endsection

@section('content')
    <div class="form-group mb-4">
        @php
            $label = 'Nama Perusahaan';
            $name = 'value[company_name]';
            $errorName = 'value.company_name';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : '')) }}

        @error($errorName)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-4">
        @php
            $label = 'Alamat Perusahaan';
            $name = 'value[address]';
            $errorName = 'value.address';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        {{ html()->textarea($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : '')) }}

        @error($errorName)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-4">
        @php
            $label = 'No. Telp';
            $name = 'value[phone_number]';
            $errorName = 'value.phone_number';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : '')) }}

        @error($errorName)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endsection

@section('footer')
    {{ html()->submit('Update')->class('btn btn-primary') }}
@endsection
