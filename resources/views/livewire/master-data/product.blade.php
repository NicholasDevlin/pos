<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Info Produk</h4>
                    <div class="row">
                        <div class="form-group col-4 mb-4">
                            @php
                                $label = 'Kategori Produk';
                                $name = 'product_category_id';
                            @endphp
                            {{ html()->label($label, $name) }}
                            <span class="text-danger">*</span>

                            <div wire:ignore>
                                {{ html()->select($name, $productCategoryList, $product_category_id)->placeholder('')->class('form-control select2')->disabled($editMode) }}
                            </div>

                            @error($name)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-4 mb-4">
                            @php
                                $label = 'Nama';
                                $name = 'name';
                            @endphp
                            {{ html()->label($label, $name) }}
                            <span class="text-danger">*</span>

                            {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'autocomplete' => 'off']) }}

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

                    <div class="form-group mb-4">
                        @php
                            $label = 'Status';
                            $name = 'status';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        @foreach ($statusList as $key => $status)
                            <div class="form-check">
                                {{ html()->radio($name, false, $key)->checked($product->{$name} !== null && $product->{$name} == $key)->class('form-check-input' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name]) }}
                                {{ html()->label($status, "{$name}_{$key}")->class('form-check-label') }}

                                @if ($loop->last && $errors->has($name))
                                    <div class="invalid-feedback">{{ $errors->get($name)[0] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Info Satuan & Harga</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bawaan</th>
                                    <th style="min-width: 150px;">Satuan <span class="text-danger">*</span></th>
                                    <th style="min-width: 150px;">SKU</th>
                                    <th>Konversi Satuan <span class="text-danger">*</span></th>
                                    <th>Harga Modal</th>
                                    <th>Harga Dasar <span class="text-danger">*</span></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $index => $detail)
                                    <tr wire:key="uom-{{ $index }}">
                                        <td class="text-center" rowspan="2">
                                            @php $name = 'default_index'; @endphp
                                            {{ html()->radio($name, null, $index)->checked($default_index == $index)->class('form-check-input' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name]) }}
                                        </td>
                                        <td>
                                            <div wire:ignore>
                                                @php $name = "details.$index.units_of_measure_id"; @endphp
                                                {{ html()->select($name, $uomList, $detail['units_of_measure_id'] ?? null)->placeholder('')->class('form-control select2') }}
                                            </div>

                                            @error($name)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php $name = "details.$index.sku"; @endphp
                                            {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'autocomplete' => 'off']) }}

                                            @error($name)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php
                                                $name = "details.$index.conversion_factor_frmt";
                                                $errorName = "details.$index.conversion_factor";
                                            @endphp
                                            {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                            @error($errorName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php
                                                $name = "details.$index.cost_price_frmt";
                                                $errorName = "details.$index.cost_price";
                                            @endphp
                                            {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                            @error($errorName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            @php
                                                $name = "details.$index.base_price_frmt";
                                                $errorName = "details.$index.base_price";
                                            @endphp
                                            {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '$money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                            @error($errorName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center" rowspan="2">
                                            @if(count($details) > 1)
                                                <button type="button" wire:click="removeUom({{ $index }})" class="btn btn-sm btn-danger"><i class="feather-trash-2"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <div class="row">
                                                @foreach($tierList as $tierKey => $tierName)
                                                    <div class="form-group col-4 mb-4">
                                                        @php
                                                            $label = "Harga $tierName";
                                                            $name = "details.$index.prices.$tierKey.price_frmt";
                                                            $errorName = "details.$index.prices.$tierKey.price";
                                                        @endphp
                                                        {{ html()->label($label, $name) }}

                                                        {{ html()->text($name)->class('form-control' . ($errors->has($errorName) ? ' is-invalid' : ''))->attributes(['wire:model.change' => $name, 'x-mask:dynamic' => '"Rp " + $money($input, ",", ".")', 'autocomplete' => 'off']) }}

                                                        @error($errorName)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            @php
                                $name = 'default_index';
                            @endphp

                            @error($name)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="button" wire:click="addUom" class="btn btn-sm btn-secondary">Tambah Satuan</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mb-3" wire:click="save">Simpan</button>
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

                    $('.select2').select2();
                });
            });
        </script>
    @endpush
</div>
