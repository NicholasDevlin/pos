<?php

namespace App\Livewire\Transaction;

use App\Helpers\ActivityLogHelper;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use App\Models\MasterData\ProductUom;
use App\Models\Transaction\Sale as SaleModel;
use App\Models\Transaction\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Sale extends Component
{
    public SaleModel $sale;

    public bool $editMode = false;

    public string $customer_id = '';

    public ?int $discount_percentage;

    public ?int $discount_nominal;

    public string $discount_nominal_frmt = '';

    public ?int $tax_percentage;

    public string $notes = '';

    public ?string $customer_tier;

    public array $details = [];

    public array $customerList = [];

    public array $products = [];

    public array $productList = [];

    public function mount(SaleModel $sale): void
    {
        $this->sale = $sale;
        $this->editMode = isset($sale->id);

        if ($this->editMode) {
            $this->fill($sale);
            $this->discount_nominal_frmt = decimal_number_format($sale->discount_nominal);

            foreach ($sale->saleItems as $item) {
                $productUom = ProductUom::find($item->product_uom_id);
                $productId = $productUom ? $productUom->product_id : '';

                $this->details[] = [
                    'id' => $item->id,
                    'product_id' => $productId,
                    'product_uom_id' => $item->product_uom_id,
                    'quantity' => $item->quantity,
                    'quantity_frmt' => decimal_number_format($item->quantity),
                    'discount_percentage' => $item->discount_percentage,
                    'discount_nominal' => $item->discount_nominal,
                    'discount_nominal_frmt' => decimal_number_format($item->discount_nominal),
                    'price' => $item->price,
                    'price_frmt' => decimal_number_format($item->price),
                ];
            }
        } else {
            $this->addItem();
        }

        $this->customerList = $this->editMode
            ? [$this->customer_id => $sale->customer->name]
            : Customer::active()->pluck('name', 'id')->all();
        $products = Product::active()
            ->orderBy('code')
            ->with(['productUoms.unitOfMeasure'])
            ->when($this->editMode, function ($query) use ($sale) {
                return $query->orWhereHas('productUoms', function ($q) use ($sale) {
                    $q->whereIn('id', $sale->saleItems->pluck('product_uom_id')->all());
                });
            })
            ->get()
            ->mapWithKeys(function ($datum) {
                return [
                    $datum->id => [
                        'id' => $datum->id,
                        'name' => $datum->name,
                        'label' => "($datum->code) - $datum->name",
                        'productUoms' => $datum->productUoms->pluck('unitOfMeasure.name', 'id')->all(),
                    ],
                ];
            });
        $this->products = $products->all();
        $this->productList = $products->pluck('label', 'id')->all();

        $this->dispatch('initialize-select2');
    }

    public function render(): View
    {
        return view('livewire.transaction.sale');
    }

    public function updatedCustomerId(): void
    {
        if (! empty($this->customer_id)) {
            $this->customer_tier = $this->getCustomerTier();
            $this->updateAllItemPrices();
        }
    }

    public function updatedDiscountNominalFrmt(): void
    {
        $this->discount_nominal = empty($this->discount_nominal_frmt) ? null : str_replace(',', '.', str_replace(['.'], '', $this->discount_nominal_frmt));
    }

    public function addItem(): void
    {
        $this->details[] = [
            'id' => null,
            'product_id' => '',
            'product_uom_id' => '',
            'quantity' => null,
            'quantity_frmt' => '',
            'discount_percentage' => null,
            'discount_nominal' => null,
            'discount_nominal_frmt' => '',
            'price' => null,
            'price_frmt' => '',
        ];

        $this->dispatch('initialize-select2');
    }

    public function removeItem($index): void
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);

        $this->dispatch('initialize-select2');
    }

    protected function getCustomerTier(): ?string
    {
        return Customer::where('id', $this->customer_id)->value('tier');
    }

    public function updateItemPrice($index): void
    {
        $uomId = $this->details[$index]['product_uom_id'] ?? null;
        if (empty($uomId)) {
            $this->details[$index]['price'] = null;
            $this->details[$index]['price_frmt'] = '';

            return;
        }

        $productUom = ProductUom::with(['tierPrices'])->find($uomId);
        if (! $productUom) {
            return;
        }

        $price = null;

        if ($this->customer_tier) {
            $tierPrice = $productUom->tierPrices->where('tier', $this->customer_tier)->first();
            if ($tierPrice && $tierPrice->price > 0) {
                $price = $tierPrice->price;
            }
        }

        if ($price === null) {
            $price = $productUom->base_price;
        }

        $this->details[$index]['price'] = $price;
        $this->details[$index]['price_frmt'] = decimal_number_format($price);
    }

    public function updateAllItemPrices(): void
    {
        foreach ($this->details as $index => $detail) {
            $this->updateItemPrice($index);
        }
    }

    public function updated($property, $value): void
    {
        if (str_starts_with($property, 'details.')) {
            $parts = explode('.', $property);
            $index = $parts[1];

            if (str_ends_with($property, '.product_id')) {
                $productId = $value;
                if (! empty($productId)) {
                    $defaultUom = ProductUom::where('product_id', $productId)->where('is_default', true)->first() ?? ProductUom::where('product_id', $productId)->first();

                    if ($defaultUom) {
                        $this->details[$index]['product_uom_id'] = $defaultUom->id;
                        $this->updateItemPrice($index);
                    } else {
                        $this->details[$index]['product_uom_id'] = '';
                        $this->details[$index]['price'] = null;
                        $this->details[$index]['price_frmt'] = '';
                    }
                } else {
                    $this->details[$index]['product_uom_id'] = '';
                    $this->details[$index]['price'] = null;
                    $this->details[$index]['price_frmt'] = '';
                }
            }

            if (str_ends_with($property, '.product_uom_id')) {
                $this->updateItemPrice($index);
            }

            $this->getValueFromFormattedInput($index);
        }

        $this->dispatch('initialize-select2');
    }

    public function getSubtotal(): float
    {
        $subtotal = 0;
        foreach ($this->details as $detail) {
            $qty = $detail['quantity'] ?? 0;
            $price = $detail['price'] ?? 0;

            $itemSubtotal = $price * $qty;

            $discountPct = $detail['discount_percentage'] ?? 0;
            $pctDiscountAmount = $itemSubtotal * ($discountPct / 100);

            $discountNominal = $detail['discount_nominal'] ?? 0;

            $itemTotal = $itemSubtotal - $pctDiscountAmount - $discountNominal;
            if ($itemTotal < 0) {
                $itemTotal = 0;
            }
            $subtotal += $itemTotal;
        }

        return $subtotal;
    }

    public function getGrandTotal(): float
    {
        $subtotal = $this->getSubtotal();

        $percentageDiscountNominal = $subtotal * (($this->discount_percentage ?? 0) / 100);

        $taxableAmount = $subtotal - $percentageDiscountNominal - ($this->discount_nominal ?? 0);
        if ($taxableAmount < 0) {
            $taxableAmount = 0;
        }

        $taxPercentage = $this->tax_percentage ?? 0;
        $taxAmount = $taxableAmount * ($taxPercentage / 100);

        return $taxableAmount + $taxAmount;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', Rule::in(array_keys($this->customerList))],
            'customer_tier' => ['required', Rule::in(array_keys(Customer::tierList()))],
            'discount_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_nominal' => ['nullable', 'integer', 'min:0'],
            'tax_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', Rule::in(array_keys($this->productList))],
            'details.*.product_uom_id' => ['required', 'distinct', 'exists:product_uoms,id'],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
            'details.*.price' => ['required', 'integer', 'min:1'],
            'details.*.discount_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'details.*.discount_nominal' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function save()
    {
        $isUserCanCreate = auth()->user()->can('sales.create');
        $isUserCanEdit = auth()->user()->can('sales.edit');

        $isAllowedToSave = $this->editMode
            ? $isUserCanEdit && ! in_array($this->sale->status, SaleModel::paymentStatuses())
            : $isUserCanCreate;

        if (! $isAllowedToSave) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->validate();

        try {
            DB::transaction(function () {
                $customer = Customer::findOrFail($this->customer_id);
                $grandTotal = $this->getGrandTotal();

                $sale = SaleModel::updateOrCreate(
                    ['id' => $this->sale->id],
                    [
                        'customer_id' => $this->customer_id,
                        'customer_name' => $customer->name,
                        'customer_address' => $customer->address,
                        'customer_tier' => $this->customer_tier,
                        'discount_percentage' => $this->discount_percentage ?? null,
                        'discount_nominal' => $this->discount_nominal ?? null,
                        'tax_percentage' => $this->tax_percentage ?? null,
                        'grand_total' => $grandTotal,
                        'notes' => $this->notes,
                    ]
                );

                $deletedSaleItemsData = $this->sale->saleItems()
                    ->whereNotIn('id', array_filter(array_column($this->details, 'id')));

                ActivityLogHelper::delete($deletedSaleItemsData);

                foreach ($this->details as $detail) {
                    $product = $this->products[$detail['product_id']];

                    SaleItem::updateOrCreate(
                        [
                            'id' => $detail['id'] ?? null,
                            'sale_id' => $sale->id,
                        ],
                        [
                            'product_uom_id' => $detail['product_uom_id'],
                            'product_name' => $product['name'],
                            'uom' => $product['productUoms'][$detail['product_uom_id']],
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price'],
                            'discount_percentage' => empty($detail['discount_percentage']) ? null : $detail['discount_percentage'],
                            'discount_nominal' => empty($detail['discount_nominal']) ? null : $detail['discount_nominal'],
                        ]
                    );
                }
            });

            session()->flash('success', 'Penjualan berhasil disimpan.');

            return redirect()->route('sales.index');
        } catch (\Exception $e) {
            report($e);

            $this->dispatch('toastr', type: 'error', message: 'Data tidak berhasil disimpan!');
        }
    }

    private function getValueFromFormattedInput(int $index): void
    {
        $detail = $this->details[$index];

        $this->details[$index]['price'] = empty($detail['price_frmt']) ? null : (int) str_replace(',', '.', str_replace(['Rp ', '.'], '', $detail['price_frmt']));
        $this->details[$index]['quantity'] = empty($detail['quantity_frmt']) ? null : (int) str_replace(',', '.', str_replace(['.'], '', $detail['quantity_frmt']));
        $this->details[$index]['discount_nominal'] = empty($detail['discount_nominal_frmt']) ? null : (int) str_replace(',', '.', str_replace(['Rp ', '.'], '', $detail['discount_nominal_frmt']));
    }
}
