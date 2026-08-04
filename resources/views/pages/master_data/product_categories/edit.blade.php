@php
    $metadata = [
        'title' => ($productCategory->id ? 'Edit' : 'Tambah') . ' Data',
    ];
@endphp

@extends('layouts.modal')

@section('start')
    {{ html()->form($productCategory->id ? 'PUT' : 'POST', $productCategory->id ? route('product-categories.update', [$productCategory->id]) : route('product-categories.store'))->attributes(['data-remote' => 'true', 'autocomplete' => 'off'])->open() }}
    @php html()->model($productCategory); @endphp
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

        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-4">
        @php
            $label = 'Status';
            $name = 'status';
        @endphp
        {{ html()->label($label, $name) }}
        <span class="text-danger">*</span>

        @foreach ($statusList as $key => $status)
            <div class="form-check">
                {{ html()->radio($name, false, $key)->checked(old($name) !== null ? old($name) == $key : $productCategory->{$name} !== null && $productCategory->{$name} == $key)->class('form-check-input' . ($errors->has($name) ? ' is-invalid' : '')) }}
                {{ html()->label($status, "{$name}_{$key}")->class('form-check-label') }}

                @if ($loop->last && $errors->has($name))
                    <div class="invalid-feedback">{{ $errors->get($name)[0] }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endsection

@section('footer')
    {{ html()->submit($productCategory->id ? 'Update' : 'Simpan')->class('btn btn-primary') }}
@endsection
