@php
    $metadata = [
        'title' => 'Edit Penagihan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Transaksi'], ['link' => route('receipts.index'), 'menu' => 'Penagihan']],
    ];
@endphp

@extends('layouts.app')

@push('styles')
    @livewireStyles
@endpush

@section('content')
    @livewire('transaction.receipt', ['sale' => $sale])
@endsection

@push('scripts')
    @livewireScripts
@endpush
