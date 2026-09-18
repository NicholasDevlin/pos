<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">Info Penjualan</div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">No. Transaksi</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->series_number }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Customer</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->customer_name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Alamat Customer</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->customer_address }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Diskon Keseluruhan (%)</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->discount_percentage ? $sale->discount_percentage . ' %' : '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Diskon Keseluruhan (Rp)</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->discount_nominal ? 'Rp ' . decimal_number_format($sale->discount_nominal) : '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Pajak</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->tax_percentage ? $sale->tax_percentage . ' %' : '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Keterangan</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $sale->notes ?: '-' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Status</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{!! $sale->statusLabel() !!}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Total Akhir (Grand Total)</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">Rp {{ decimal_number_format($sale->grand_total) }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-title">Penagihan</div>

                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 10%;">No.</th>
                                            <th style="width: 75%;">Nominal Pembayaran</th>
                                            <th style="width: 15%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receipts as $index => $receipt)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    @php
                                                        $name = "receipts.$index.paid_amount_frmt";
                                                        $errorName = "receipts.$index.paid_amount";
                                                    @endphp
                                                    {{ html()->text($name)->class('form-control text-right' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                                    @error($errorName)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td class="text-center htMiddle">
                                                    @if(count($receipts) > 1)
                                                        <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-danger"><i class="feather-trash-2"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <button type="button" wire:click="addItem" class="btn btn-sm btn-secondary mb-4">Tambah Baris</button>

                    @error('receipts')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror

                    <hr>

                    <button type="submit" class="btn btn-primary mb-3" wire:click="save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
