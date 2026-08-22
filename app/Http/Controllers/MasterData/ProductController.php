<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogHelper;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
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
        return Product::with('productCategory')
            ->orderByDesc('updated_at')
            ->get(['id', 'product_category_id', 'code', 'name', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('products.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    "<a class='btn btn-xs btn-secondary' href='".route('products.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('products.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>",
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
