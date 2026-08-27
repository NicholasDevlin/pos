<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Info Penjualan</h4>
                    <div class="row">
                        <div class="form-group col-md-6 col-12 mb-4">
                            @php
                                $label = 'Customer';
                                $name = 'customer_id';
                            @endphp
                            {{ html()->label($label, $name) }}
                            <span class="text-danger">*</span>

                            <div wire:ignore>
                                {{ html()->select($name, $customerList, $customer_id)->placeholder('')->class('form-control select2')->disabled($editMode) }}
                            </div>

                            @error($name)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3 col-6 mb-4">
                            @php
                                $label = 'Diskon Keseluruhan (%)';
                                $name = 'discount_percentage';
                            @endphp
                            {{ html()->label($label, $name) }}

                            {{ html()->number($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'min' => 0, 'max' => 100, 'autocomplete' => 'off']) }}

                            @error($name)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-3 col-6 mb-4">
                            @php
                                $label = 'Diskon Keseluruhan (Rp)';
                                $name = 'discount_nominal_frmt';
                                $errorName = 'discount_nominal';
                            @endphp
                            {{ html()->label($label, $name) }}

                            {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                            @error($errorName)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-3 col-6 mb-4">
                            @php
                                $label = 'Pajak (%)';
                                $name = 'tax_percentage';
                            @endphp
                            {{ html()->label($label, $name) }}

                            {{ html()->number($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'min' => 0, 'max' => 100, 'autocomplete' => 'off']) }}

                            @error($name)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        @php
                            $label = 'Keterangan';
                            $name = 'notes';
                        @endphp
                        {{ html()->label($label, $name) }}

                        {{ html()->textarea($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'autocomplete' => 'off']) }}

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row {{ $customer_id ? '' : 'd-none' }}">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Item Penjualan</h4>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm table-bordered" style="table-layout: fixed;">
                            <colgroup>
                                <col style="width: 40px;"> {{-- No. --}}
                                <col style="width: 280px;"> {{-- Produk --}}
                                <col style="width: 150px;"> {{-- Satuan --}}
                                <col style="width: 130px;"> {{-- Harga Satuan --}}
                                <col style="width: 80px;"> {{-- Qty. --}}
                                <col style="width: 80px;"> {{-- Diskon % --}}
                                <col style="width: 130px;"> {{-- Diskon Nominal --}}
                                <col style="width: 200px;"> {{-- Total --}}
                                <col style="width: 50px;"> {{--  --}}
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="text-center align-content-center" rowspan="2">No.</th>
                                    <th class="align-content-center" rowspan="2">Produk <span class="text-danger">*</span></th>
                                    <th class="align-content-center" rowspan="2">Satuan <span class="text-danger">*</span></th>
                                    <th class="text-center align-content-center" rowspan="2">Harga Satuan <span class="text-danger">*</span></th>
                                    <th class="text-center align-content-center" rowspan="2">Qty. <span class="text-danger">*</span></th>
                                    <th class="text-center align-content-center" colspan="2">Diskon</th>
                                    <th class="text-right align-content-center" rowspan="2">Total</th>
                                    <th class="text-center align-content-center" rowspan="2"></th>
                                </tr>
                                <tr>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $index => $detail)
                                    @php
                                        $qty = $detail['quantity'] ?? 0;
                                        $price = $detail['price'];
                                        $itemSubtotal = $price * $qty;

                                        $discountPct = $detail['discount_percentage'] ?: 0;
                                        $pctDiscountAmount = $itemSubtotal * ($discountPct / 100);

                                        $discountNominal = $detail['discount_nominal'];

                                        $itemTotal = $itemSubtotal - $pctDiscountAmount - $discountNominal;
                                        if ($itemTotal < 0) {
                                            $itemTotal = 0;
                                        }
                                    @endphp
                                    <tr wire:key="item-{{ $index }}-{{ $detail['product_id'] ?? '' }}-{{ $detail['product_uom_id'] ?? '' }}">
                                        <td class="text-center htMiddle">
                                            {{ $index + 1 }}
                                        </td>
                                        <td>
                                            <div wire:key="product-select-{{ $index }}-{{ $detail['product_id'] ?? '' }}" wire:ignore>
                                                @php $name = "details.$index.product_id"; @endphp
                                                {{ html()->select($name, $productList, $detail['product_id'] ?? null)->placeholder('')->class('form-control select2') }}
                                            </div>

                                            @error($name)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <div wire:key="uom-select-{{ $index }}-{{ $detail['product_id'] ?? '' }}-{{ $detail['product_uom_id'] ?? '' }}" wire:ignore>
                                                @php
                                                    $name = "details.$index.product_uom_id";
                                                    $uomOptions = empty($detail['product_id']) ? [] : $products[$detail['product_id']]['productUoms'];
                                                @endphp
                                                {{ html()->select($name, $uomOptions, $detail['product_uom_id'] ?? null)->placeholder('')->class('form-control select2') }}
                                            </div>

                                            @error($name)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php
                                                $name = "details.$index.price_frmt";
                                                $errorName = "details.$index.price";
                                            @endphp
                                            {{ html()->text($name)->class('form-control text-right' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                            @error($errorName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php
                                                $name = "details.$index.quantity_frmt";
                                                $errorName = "details.$index.quantity";
                                            @endphp
                                            {{ html()->text($name)->class('form-control text-right' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                            @error($errorName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php $name = "details.$index.discount_percentage"; @endphp
                                            {{ html()->number($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'min' => 0, 'max' => 100, 'autocomplete' => 'off']) }}

                                            @error($name)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php
                                                $name = "details.$index.discount_nominal_frmt";
                                                $errorName = "details.$index.discount_nominal";
                                            @endphp
                                            {{ html()->text($name)->class('form-control text-right' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                            @error($errorName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-right font-weight-bold htMiddle">
                                            Rp {{ decimal_number_format($itemTotal) }}
                                        </td>
                                        <td class="text-center htMiddle">
                                            @if(count($details) > 1)
                                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-danger"><i class="feather-trash-2"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <button type="button" wire:click="addItem" class="btn btn-sm btn-secondary mb-4">Tambah Item</button>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-5 col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Ringkasan Pembayaran</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        @php
                                            $subtotal = $this->getSubtotal();
                                        @endphp
                                        <span>Subtotal:</span>
                                        <span class="font-weight-bold">Rp {{ decimal_number_format($subtotal) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Diskon Keseluruhan (%):</span>
                                        @php
                                            $percentageDiscountNominal = $subtotal * (($discount_percentage ?? 0) / 100);
                                        @endphp
                                        <span class="text-danger font-weight-bold">- Rp {{ decimal_number_format($percentageDiscountNominal) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Diskon Keseluruhan (Rp):</span>
                                        <span class="text-danger font-weight-bold">- Rp {{ decimal_number_format($discount_nominal ?? 0) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pajak ({{ $tax_percentage }}%):</span>
                                        @php
                                            $taxable = $subtotal - $percentageDiscountNominal - $discount_nominal;
                                            if ($taxable < 0) $taxable = 0;
                                            $taxNominal = $taxable * ($tax_percentage / 100);
                                        @endphp
                                        <span class="font-weight-bold">Rp {{ decimal_number_format($taxNominal) }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between font-weight-bold" style="font-size: 1.1rem;">
                                        <span>Total Akhir:</span>
                                        <span class="text-success">Rp {{ decimal_number_format($this->getGrandTotal()) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mb-3" wire:click="save">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('initialize-select2', () => {
                $(function() {
                    $('.select2').off('change').on('change', function() {
                        const data = $(this).select2('val');
                        @this.set($(this).attr('name'), data);
                    });

                    $('.select2').select2({
                        matcher: function(params, data) {
                            if ($.trim(params.term) === '') {
                                return data;
                            }

                            if (typeof data.text === 'undefined') {
                                return null;
                            }

                            if (fuzzyMatch(params.term, data.text)) {
                                const newData = $.extend({}, data, true);
                                newData.searchTerm = params.term;
                                return newData;
                            }

                            return null;
                        },
                        templateResult: function(data) {
                            if (!data.id || !data.searchTerm) {
                                return data.text;
                            }

                            return highlightFuzzyMatch(data.searchTerm, data.text);
                        }
                    });
                });
            });

            function fuzzyMatch(query, text) {
                query = query.toLowerCase();
                text = text.toLowerCase();

                let queryIndex = 0;
                for (let i = 0; i < text.length && queryIndex < query.length; i++) {
                    if (text[i] === query[queryIndex]) {
                        queryIndex++;
                    }
                }
                return queryIndex === query.length;
            }

            function highlightFuzzyMatch(query, text) {
                if (!query) {
                    return $('<span></span>').text(text);
                }

                query = query.toLowerCase();
                const lowerText = text.toLowerCase();

                let queryIndex = 0;
                let html = '';

                for (let i = 0; i < text.length; i++) {
                    const char = text[i];

                    if (queryIndex < query.length && lowerText[i] === query[queryIndex]) {
                        html += `<mark style="background-color: yellow; padding: 0;">${char}</mark>`;
                        queryIndex++;
                    } else {
                        html += char;
                    }
                }

                return $('<span></span>').html(html);
            }
        </script>
    @endpush
</div>
