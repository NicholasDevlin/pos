@php
    $metadata = [
        'title' => ($product->id ? 'Edit' : 'Tambah') . ' Produk',
        'breadcrumb' => [['link' => '#', 'menu' => 'Transaksi'], ['link' => route('products.index'), 'menu' => 'Produk']],
    ];
@endphp

@extends('layouts.app')

@push('styles')
    @livewireStyles
@endpush

@section('content')
    @livewire('master_data.product', ['product' => $product])
@endsection

@push('scripts')
    @livewireScripts
@endpush
