<?php

namespace App\Http\Controllers\Transaction;

use App\Helpers\ActivityLogHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\CompanyProfileRequest;
use App\Models\Option;
use App\Models\Transaction\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['ajax'])->only(['editCompanyProfile']);
        $this->middleware(['permission:sales.show'])->only(['index', 'show']);
        $this->middleware(['permission:sales.print'])->only(['initiatePrinting']);
        $this->middleware(['permission:sales.edit'])->only(['edit', 'editCompanyProfile', 'delivered', 'received']);
    }

    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view(view: 'pages.transactions.sales.index');
    }

    private function tableData(): Collection
    {
        $isUserCanShowAllData = auth()->user()->can('sales.show-all');
        $isUserCanEdit = auth()->user()->can('sales.edit');
        $isUserCanPrint = auth()->user()->can('sales.print');
        $isUserCanDelete = auth()->user()->can('sales.delete');

        return Sale::when(! $isUserCanShowAllData, fn ($q) => $q->oddId())
            ->orderByDesc('updated_at')
            ->get(['id', 'customer_name', 'customer_address', 'series_number', 'grand_total', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) use ($isUserCanEdit, $isUserCanPrint, $isUserCanDelete) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('sales.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    $isUserCanEdit && ! $datum->isStatusUnpaid() ? "<a class='btn btn-xs btn-secondary' href='".route('sales.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>" : null,
                    $isUserCanPrint ? "<a class='btn btn-xs btn-info' data-remote='true' href='".route('sales.print', [$datum->id])."' title='Print'><i class='feather-printer text-white'></i></a>" : null,
                    $isUserCanEdit && $datum->isStatusNew() ? "<a class='btn btn-xs btn-warning' data-remote='true' href='".route('sales.delivered', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='post' data-confirm='Apakah Anda yakin Penjualan ini sudah dikirimkan?' title='Terkirim'><i class='feather-truck text-white'></i></a>" : null,
                    $isUserCanEdit && ! $datum->isStatusUnpaid()  ? "<a class='btn btn-xs btn-dark' data-remote='true' href='".route('sales.received', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='post' data-confirm='Apakah Anda yakin Penjualan ini sudah diterima?' title='Diterima Customer'><i class='feather-archive text-white'></i></a>" : null,
                    $isUserCanDelete && $datum->isStatusNew() ? "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('sales.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>" : null,
                ]));

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    public function create(): View
    {
        $sale = new Sale;

        return view('pages.transactions.sales.edit', compact('sale'));
    }

    public function show(Sale $sale): View
    {
        return view('pages.transactions.sales.show', compact('sale'));
    }

    public function edit(Sale $sale): View
    {
        $sale->load(['customer', 'saleItems']);

        return view('pages.transactions.sales.edit', compact('sale'));
    }

    public function destroy(Sale $sale): string
    {
        DB::beginTransaction();

        try {
            ActivityLogHelper::delete($sale->saleItems());

            $sale->delete();

            DB::commit();

            session()->flash('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            report($e);

            DB::rollBack();
            session()->flash('fail', 'Data tidak berhasil dihapus!');
        }

        return "<script>window.location='".route('sales.index')."'</script>";
    }

    public function editCompanyProfile(): View
    {
        $option = Option::where('name', Option::COMPANY_PROFILE)->first() ?? new Option;

        if ($option->id) {
            $option->value = json_decode($option->value, true);
        }

        return view('pages.transactions.sales.edit_company_profile', compact('option'));
    }

    public function updateCompanyProfile(CompanyProfileRequest $request): string
    {
        $validated = $request->validated();

        Option::updateOrCreate(
            ['name' => Option::COMPANY_PROFILE],
            ['value' => json_encode($validated['value'])],
        );

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('sales.index')."'</script>";
    }

    public function initiatePrinting(Sale $sale): View
    {
        $option = Option::where('name', Option::COMPANY_PROFILE)->first() ?? new Option;
        $companyProfile = $option->value ? json_decode($option->value, true) : [];

        $saleItems = $sale->saleItems;
        $chunkSize = 33;

        $rawChunks = $saleItems->chunk($chunkSize)->values();
        $lastIndex = $rawChunks->count() - 1;

        $saleItemChunks = $rawChunks->map(function ($chunk, $index) use ($lastIndex) {
            $isLastChunk = $index === $lastIndex;
            $useA5 = $isLastChunk && $chunk->count() <= 10;

            return (object) [
                'items' => $chunk,
                'pageName' => $useA5 ? 'a5page' : 'a4page',
                'chunkLength' => $useA5 ? 10 : 33,
            ];
        });

        return view('pages.transactions.sales.print', compact('sale', 'companyProfile', 'saleItemChunks'));
    }

    public function delivered(Sale $sale): string
    {
        if (! $sale->isStatusNew()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $sale->status = Sale::STATUS_DELIVERED;
        $sale->save();

        return "<script>window.location='".route('sales.index')."'</script>";
    }

    public function received(Sale $sale): string
    {
        if (! in_array($sale->status, [Sale::STATUS_NEW, Sale::STATUS_DELIVERED])) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $sale->status = Sale::STATUS_UNPAID;
        $sale->save();

        return "<script>window.location='".route('sales.index')."'</script>";
    }
}
