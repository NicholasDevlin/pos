<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:receipts.show'])->only(['index', 'show']);
        $this->middleware(['permission:receipts.edit'])->only(['edit']);
    }

    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view(view: 'pages.transactions.receipts.index');
    }

    private function tableData(): Collection
    {
        $isUserCanShowAllData = auth()->user()->can('sales.show-all');
        $isUserCanEdit = auth()->user()->can('receipts.edit');

        return Sale::when(! $isUserCanShowAllData, fn ($q) => $q->oddId())
            ->orderByDesc('updated_at')
            ->whereIn('status', Sale::paymentStatuses())
            ->with(['receipts'])
            ->get(['id', 'series_number', 'customer_name', 'customer_address', 'grand_total', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) use ($isUserCanEdit,) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('receipts.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    $isUserCanEdit && in_array($datum->status, [Sale::STATUS_UNPAID, Sale::STATUS_PARTIALLY_PAID]) ? "<a class='btn btn-xs btn-secondary' href='".route('receipts.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>" : null,
                ]));

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    public function show(Sale $sale): view
    {
        $sale->load('receipts');

        return view('pages.transactions.receipts.show', compact('sale'));
    }

    public function edit(Sale $sale): View
    {
        if (! in_array($sale->status, [Sale::STATUS_UNPAID, Sale::STATUS_PARTIALLY_PAID])) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $sale->load('receipts');

        return view('pages.transactions.receipts.edit', compact('sale'));
    }
}
