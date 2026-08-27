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

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['ajax'])->only(['editCompanyProfile']);
        $this->middleware(['permission:sales.show'])->only(['index', 'show']);
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
        return Sale::orderByDesc('updated_at')
            ->get(['id', 'customer_name', 'customer_address', 'series_number', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('sales.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    "<a class='btn btn-xs btn-secondary' href='".route('sales.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-info' data-remote='true' href='".route('sales.print', [$datum->id])."' title='Print'><i class='feather-printer text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('sales.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>",
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
        $chunkSize = 30;

        $rawChunks = $saleItems->chunk($chunkSize)->values();
        $lastIndex = $rawChunks->count() - 1;

        $saleItemChunks = $rawChunks->map(function ($chunk, $index) use ($lastIndex) {
            $isLastChunk = $index === $lastIndex;
            $useA5 = $isLastChunk && $chunk->count() <= 10;

            return (object) [
                'items' => $chunk,
                'pageName' => $useA5 ? 'a5page' : 'a4page',
                'chunkLength' => $useA5 ? 10 : 30,
            ];
        });

        return view('pages.transactions.sales.print', compact('sale', 'companyProfile', 'saleItemChunks'));
    }
}
