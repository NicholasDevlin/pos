<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\ProductCategoryRequest;
use App\Models\MasterData\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['ajax'])->only(['create', 'show', 'edit']);
        $this->middleware(['permission:product-categories.show'])->only(['index', 'show']);
    }

    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.master_data.product_categories.index');
    }

    private function tableData(): Collection
    {
        $isUserCanEdit = auth()->user()->can('customers.edit');
        $isUserCanDelete = auth()->user()->can('customers.delete');

        return ProductCategory::orderByDesc('updated_at')
            ->leftJoinSub(DB::table('products')->distinct()->select('product_category_id AS products_product_category_id'), 'products', 'products.products_product_category_id', 'product_categories.id')
            ->get(['id', 'code', 'name', 'notes', 'status', 'created_at', 'updated_at',
                DB::raw('products_product_category_id IS NULL AS is_editable')
            ])
            ->map(function ($datum) use ($isUserCanEdit, $isUserCanDelete) {
                $datum->actions = implode(' ', array_filter([
                    $isUserCanEdit && $datum->is_editable ? "<a class='btn btn-xs btn-secondary' data-remote='true' href='".route('product-categories.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>" : null,
                    $isUserCanDelete && $datum->is_editable ? "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('product-categories.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>" : null,
                ]));

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    public function create(): View
    {
        $productCategory = new ProductCategory;
        $statusList = ProductCategory::statusList();

        return view('pages.master_data.product_categories.edit', compact('productCategory', 'statusList'));
    }

    public function store(ProductCategoryRequest $request): string
    {
        ProductCategory::create($request->validated());

        session()->flash('success', 'Data berhasil disimpan!');

        return "<script>window.location='".route('product-categories.index')."'</script>";
    }

    public function edit(ProductCategory $productCategory): View
    {
        $statusList = ProductCategory::statusList();

        return view('pages.master_data.product_categories.edit', compact('productCategory', 'statusList'));
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): string
    {
        $productCategory->update($request->validated());

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('product-categories.index')."'</script>";
    }

    public function destroy(ProductCategory $productCategory): string
    {
        $productCategory->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('product-categories.index')."'</script>";
    }
}
