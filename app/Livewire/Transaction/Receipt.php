<?php

namespace App\Livewire\Transaction;

use App\Helpers\ActivityLogHelper;
use App\Models\Transaction\Receipt as ReceiptModel;
use App\Models\Transaction\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Receipt extends Component
{
    public Sale $sale;

    public array $receipts = [];

    protected $validationAttributes = [
        'receipts.*.paid_amount' => 'Nominal Pembayaran',
    ];

    public function mount(Sale $sale): void
    {
        $this->sale = $sale;

        $saleReceipts = $sale->receipts;
        if ($saleReceipts->isNotEmpty()) {
            foreach ($saleReceipts as $item) {
                $this->receipts[] = [
                    'id' => $item->id,
                    'paid_amount' => $item->paid_amount,
                    'paid_amount_frmt' => decimal_number_format($item->paid_amount),
                ];
            }
        } else {
            $this->addItem();
        }
    }

    public function render(): View
    {
        return view('livewire.transaction.receipt');
    }

    public function addItem(): void
    {
        $this->receipts[] = [
            'id' => null,
            'paid_amount' => null,
            'paid_amount_frmt' => '',
        ];
    }

    public function removeItem($index): void
    {
        unset($this->receipts[$index]);
        $this->receipts = array_values($this->receipts);
    }

    private function prepareDataBeforeValidation(): void
    {
        foreach ($this->receipts as $index => $receipt) {
            $this->getValueFromFormattedInput($index);
        }
    }

    public function rules(): array
    {
        $this->prepareDataBeforeValidation();

        return [
            'receipts' => ['required', 'array', 'min:1'],
            'receipts.*.paid_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function updated($property, $value): void
    {
        if (str_starts_with($property, 'receipts.')) {
            $parts = explode('.', $property);
            $index = $parts[1];

            $this->getValueFromFormattedInput($index);
        }
    }

    private function getValueFromFormattedInput(int $index): void
    {
        $receipt = $this->receipts[$index];

        $this->receipts[$index]['paid_amount'] = ($receipt['paid_amount_frmt'] === null || $receipt['paid_amount_frmt'] === '')
            ? null
            : (float) str_replace(',', '.', str_replace(['.'], '', $receipt['paid_amount_frmt']));
    }

    public function save()
    {
        $isUserCanEdit = auth()->user()->can('receipts.edit');

        $sale = Sale::find($this->sale->id);
        if (! ($isUserCanEdit && in_array($sale->status, [Sale::STATUS_UNPAID, Sale::STATUS_PARTIALLY_PAID]))) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->validate();

        $totalPaid = array_sum(array_column($this->receipts, 'paid_amount'));

        if ($totalPaid > $this->sale->grand_total) {
            $this->addError('receipts', 'Total nominal pembayaran tidak boleh melebihi Total Akhir (Grand Total) Rp '.decimal_number_format($this->sale->grand_total).'.');

            return;
        }

        try {
            DB::transaction(function () use ($totalPaid) {
                $deletedReceipts = $this->sale->receipts()
                    ->whereNotIn('id', array_filter(array_column($this->receipts, 'id')));

                ActivityLogHelper::delete($deletedReceipts);

                foreach ($this->receipts as $receipt) {
                    ReceiptModel::updateOrCreate(
                        [
                            'id' => $receipt['id'] ?? null,
                            'sale_id' => $this->sale->id,
                        ],
                        [
                            'paid_amount' => $receipt['paid_amount'],
                        ]
                    );
                }

                if ($totalPaid < $this->sale->grand_total) {
                    $this->sale->status = Sale::STATUS_PARTIALLY_PAID;
                } else {
                    $this->sale->status = Sale::STATUS_PAID;
                }

                $this->sale->save();
            });

            session()->flash('success', 'Penagihan berhasil disimpan.');

            return redirect()->route('receipts.index');
        } catch (\Exception $e) {
            report($e);

            $this->dispatch('toastr', type: 'error', message: 'Data tidak berhasil disimpan!');
        }
    }
}
