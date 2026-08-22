<?php

namespace App\Livewire\MasterData;

use App\Helpers\ActivityLogHelper;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product as ProductModel;
use App\Models\MasterData\ProductCategory;
use App\Models\MasterData\ProductTierPrice;
use App\Models\MasterData\ProductUom;
use App\Models\MasterData\UnitOfMeasure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Product extends Component
{
    public ProductModel $product;

    public array $productCategoryList;

    public array $uomList;

    public array $tierList;

    public array $statusList;

    public $editMode = false;

    public string $product_category_id = '';

    public string $name = '';

    public string $notes = '';

    public string $status;

    public string $default_index = '0';

    public array $details = [];

    public function mount(ProductModel $product): void
    {
        $this->editMode = isset($product->id);
        $this->status = ProductModel::STATUS_ACTIVE;

        if ($this->editMode) {
            $this->fill($product);

            foreach ($product->productUoms as $index => $uom) {
                $uomData = [
                    'id' => $uom->id,
                    'units_of_measure_id' => $uom->units_of_measure_id,
                    'sku' => $uom->sku,
                    'conversion_factor' => $uom->conversion_factor,
                    'conversion_factor_frmt' => decimal_number_format($uom->conversion_factor),
                    'cost_price' => $uom->cost_price,
                    'cost_price_frmt' => decimal_number_format($uom->cost_price),
                    'base_price' => $uom->base_price,
                    'base_price_frmt' => decimal_number_format($uom->base_price),
                    'prices' => [],
                ];

                if ($uom->is_default) {
                    $this->default_index = $index;
                }

                $productTierPrices = $uom->tierPrices->keyBy('tier');
                foreach (Customer::tierList() as $tierKey => $tierName) {
                    $price = $productTierPrices[$tierKey]->price ?? 0;

                    $uomData['prices'][$tierKey] = [
                        'tier' => $tierKey,
                        'price' => $price,
                        'price_frmt' => decimal_number_format($price),
                    ];
                }

                $this->details[] = $uomData;
            }
        } else {
            $this->addUom();
            $this->default_index = '0';
        }

        $this->productCategoryList = ProductCategory::active()->pluck('name', 'id')->all();
        $this->uomList = UnitOfMeasure::active()->pluck('name', 'id')->all();
        $this->tierList = Customer::tierList();
        $this->statusList = ProductModel::statusList();

        $this->dispatch('initialize-select2');
    }

    public function render(): View
    {
        return view('livewire.master-data.product');
    }

    public function addUom(): void
    {
        $prices = [];
        foreach (Customer::tierList() as $tierKey => $tierName) {
            $prices[$tierKey] = [
                'tier' => $tierKey,
                'price' => null,
                'price_frmt' => '',
            ];
        }

        $this->details[] = [
            'id' => null,
            'units_of_measure_id' => '',
            'sku' => '',
            'conversion_factor' => null,
            'conversion_factor_frmt' => '',
            'cost_price' => null,
            'cost_price_frmt' => '',
            'base_price' => null,
            'base_price_frmt' => '',
            'prices' => $prices,
        ];

        $this->dispatch('initialize-select2');
    }

    public function removeUom($index): void
    {
        unset($this->details[$index]);

        $this->dispatch('initialize-select2');
    }

    public function prepareDataForValidation(): void
    {
        $this->details = collect($this->details)
            ->map(function ($detail) {
                $detail['conversion_factor'] = str_replace(',', '.', str_replace(['.'], '', $detail['conversion_factor_frmt']));
                $detail['cost_price'] = empty($detail['cost_price_frmt']) ? null : str_replace(',', '.', str_replace(['Rp ', '.'], '', $detail['cost_price_frmt']));
                $detail['base_price'] = str_replace(',', '.', str_replace(['Rp ', '.'], '', $detail['base_price_frmt']));

                foreach (Customer::tierList() as $tierKey => $tierName) {
                    $detail['prices'][$tierKey]['price'] = empty($detail['prices'][$tierKey]['price_frmt']) ? 0 : str_replace(',', '.', str_replace(['Rp ', '.'], '', $detail['prices'][$tierKey]['price_frmt']));
                }

                return $detail;
            })
            ->all();
    }

    public function rules(): array
    {
        $this->prepareDataForValidation();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', Rule::in(array_keys($this->productCategoryList))],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys($this->statusList))],

            'default_index' => ['required', Rule::in(array_keys($this->details))],

            'details' => ['required', 'array', 'min:1'],

            'details.*.units_of_measure_id' => ['required', 'distinct', Rule::in(array_keys($this->uomList))],
            'details.*.sku' => ['nullable', 'string', 'distinct', 'max:255'],
            'details.*.conversion_factor' => ['required', 'integer', 'min:1'],
            'details.*.cost_price' => ['nullable', 'integer', 'min:0'],
            'details.*.base_price' => ['required', 'integer', 'min:0'],

            'details.*.prices.*.tier' => ['required', Rule::in(array_keys($this->tierList))],
            'details.*.prices.*.price' => ['required', 'integer', 'min:0'],
        ];

        foreach ($this->details as $index => $detail) {
            if (empty($detail['sku'])) {
                continue;
            }

            $rules["details.$index.sku"][] = Rule::unique('product_uoms', 'sku')->ignore($detail['id'] ?? null, 'id');
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $codePrefix = ProductCategory::where('id', $this->product_category_id)->value('code') ?? 'PRD';

                $product = ProductModel::updateOrCreate(
                    ['id' => $this->product->id],
                    [
                        ...$this->editMode
                            ? []
                            : [
                                'code' => "$codePrefix-",
                                'product_category_id' => $this->product_category_id,
                            ],
                        'name' => $this->name,
                        'notes' => $this->notes,
                        'status' => $this->status,
                    ]
                );

                $existingUomIds = $product->productUoms()->pluck('id')->toArray();
                $currentUomIds = [];

                foreach ($this->details as $index => $uomData) {
                    $uom = ProductUom::updateOrCreate(
                        ['id' => $uomData['id'], 'product_id' => $product->id],
                        [
                            'units_of_measure_id' => $uomData['units_of_measure_id'],
                            'sku' => $uomData['sku'] ?: null,
                            'conversion_factor' => $uomData['conversion_factor'],
                            'cost_price' => $uomData['cost_price'],
                            'base_price' => $uomData['base_price'],
                            'is_default' => $this->default_index == $index,
                        ]
                    );
                    $currentUomIds[] = $uom->id;

                    foreach ($uomData['prices'] as $tier => $priceData) {
                        ProductTierPrice::updateOrCreate(
                            ['product_uom_id' => $uom->id, 'tier' => $tier],
                            ['price' => $priceData['price']]
                        );
                    }
                }

                $uomsToDelete = array_diff($existingUomIds, $currentUomIds);

                if (! empty($uomsToDelete)) {
                    $productTierPrices = ProductTierPrice::whereIn('product_uom_id', $uomsToDelete);
                    ActivityLogHelper::delete($productTierPrices);

                    $productUoms = ProductUom::whereIn('id', $uomsToDelete);
                    ActivityLogHelper::delete($productUoms);
                }
            });

            session()->flash('success', 'Produk berhasil disimpan.');

            return redirect()->route('products.index');
        } catch (\Exception $e) {
            report($e);

            $this->dispatch('toastr', type: 'error', message: 'Data tidak berhasil disimpan!');
        }
    }
}
