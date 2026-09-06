<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogHelper;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:products.show'])->only(['index', 'show']);
    }

    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.master_data.products.index');
    }

    private function tableData(): Collection
    {
        $isUserCanEdit = auth()->user()->can('products.delete');
        $isUserCanDelete = auth()->user()->can('products.delete');

        return Product::with('productCategory')
            ->orderByDesc('updated_at')
            ->leftJoinSub(
                DB::table('sale_items')
                    ->join('product_uoms', 'product_uoms.id', 'sale_items.product_uom_id')
                    ->distinct()
                    ->select('product_uoms.product_id AS sale_items_product_id'),
                'sale_items',
                'sale_items.sale_items_product_id',
                'products.id'
            )
            ->get(['id', 'product_category_id', 'code', 'name', 'notes', 'status', 'created_at', 'updated_at',
                DB::raw('sale_items_product_id IS NULL AS is_editable')
            ])
            ->map(function ($datum) use ($isUserCanEdit, $isUserCanDelete) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('products.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    $isUserCanEdit ? "<a class='btn btn-xs btn-secondary' href='".route('products.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>" : null,
                    $isUserCanDelete && $datum->is_editable ? "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('products.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>" : null,
                ]));

                $datum->category_name = $datum->productCategory->name;
                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    public function create(): View
    {
        $product = new Product;

        return view('pages.master_data.products.edit', compact('product'));
    }

    public function show(Product $product): View
    {
        $product->load(['productCategory', 'productUoms.unitOfMeasure', 'productUoms.tierPrices']);
        $tierList = Customer::tierList();

        return view('pages.master_data.products.show', compact('product', 'tierList'));
    }

    public function edit(Product $product): View
    {
        $product->load(['productCategory', 'productUoms.unitOfMeasure', 'productUoms.tierPrices']);

        return view('pages.master_data.products.edit', compact('product'));
    }

    public function destroy(Product $product): string
    {
        $isUsedInSaleItems = DB::table('sale_items')
            ->join('product_uoms', 'product_uoms.id', 'sale_items.product_uom_id')
            ->where('product_uoms.product_id', $product->id)
            ->exists();

        if ($isUsedInSaleItems) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $product->load(['productUoms']);

        DB::beginTransaction();

        try {
            foreach ($product->productUoms as $productUom) {
                ActivityLogHelper::delete($productUom->tierPrices());

                $productUom->delete();
            }

            $product->delete();

            DB::commit();

            session()->flash('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            report($e);

            DB::rollBack();
            session()->flash('fail', 'Data tidak berhasil dihapus!');
        }

        return "<script>window.location='".route('products.index')."'</script>";
    }
}
