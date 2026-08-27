@php
    $metadata = [
        'title' => ($sale->id ? 'Edit' : 'Tambah') . ' Penjualan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Transaksi'], ['link' => route('sales.index'), 'menu' => 'Penjualan']],
    ];
@endphp

@extends('layouts.app')

@push('styles')
    @livewireStyles
@endpush

@section('content')
    @livewire('transaction.sale', ['sale' => $sale])
@endsection

@push('scripts')
    @livewireScripts
@endpush
