@php
    $metadata = [
        'title' => ($option->id ? 'Edit' : 'Tambah') . ' Data',
    ];
@endphp

@extends('layouts.modal')

@section('start')
    {{ html()->form($option->id ? 'PUT' : 'POST', $option->id ? route('options.update', [$option->id]) : route('options.store'))->attributes(['data-remote' => 'true', 'autocomplete' => 'off'])->open() }}
    @php html()->model($option); @endphp
@endsection

@section('end')
    {{ html()->form()->close() }}
@endsection

@section('content')
    <div class="form-group mb-2">
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

    <div class="form-group mb-2">
        @php
            $label = 'Nilai';
            $name = 'value';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-2">
        @php
            $label = 'Keterangan';
            $name = 'notes';
        @endphp
        {{ html()->label($label, $name) }}

        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-2">
        @php
            $label = 'Status';
            $name = 'status';
        @endphp
        {{ html()->label($label, $name) }}

        @foreach ($statusList as $key => $status)
            <div class="form-check">
                {{ html()->radio($name, false, $key)->checked($option->{$name} !== null && $option->{$name} == $key)->class('form-check-input' . ($errors->has($name) ? ' is-invalid' : '')) }}
                {{ html()->label($status, "{$name}_{$key}")->class('form-check-label') }}
            </div>
        @endforeach

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endsection

@section('footer')
    {{ html()->submit($option->id ? 'Update' : 'Simpan')->class('btn btn-primary') }}
@endsection
