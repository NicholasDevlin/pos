@php
    $metadata = [
        'title' => ($permission->id ? 'Edit' : 'Tambah') . ' Data',
    ];
@endphp

@extends('layouts.modal')

@section('start')
    {{ html()->form($permission->id ? 'PUT' : 'POST', $permission->id ? route('permissions.update', [$permission->id]) : route('permissions.store'))->attributes(['data-remote' => 'true', 'autocomplete' => 'off'])->open() }}
    @php html()->model($permission); @endphp
@endsection

@section('end')
    {{ html()->form()->close() }}
@endsection

@section('content')
    <div class="form-group mb-4">
        @php
            $label = 'Nama';
            $name = 'name';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-4">
        @php
            $label = 'Keterangan';
            $name = 'notes';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endsection

@section('footer')
    {{ html()->submit($permission->id ? 'Update' : 'Simpan')->class('btn btn-primary') }}
@endsection
