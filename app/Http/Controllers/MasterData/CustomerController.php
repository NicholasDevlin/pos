<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\CustomerRequest;
use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['ajax'])->only(['create', 'show', 'edit']);
        $this->middleware(['permission:customers.show'])->only(['index', 'show']);
    }

    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.master_data.customers.index');
    }

    private function tableData(): Collection
    {
        return Customer::orderByDesc('updated_at')
            ->get(['id', 'name', 'phone', 'address', 'tier', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-secondary' data-remote='true' href='".route('customers.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('customers.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>",
                ]));

                $datum->tier = $datum->tierLabel();
                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    public function create(): View
    {
        $customer = new Customer;
        $tierList = Customer::tierList();
        $statusList = Customer::statusList();

        return view('pages.master_data.customers.edit', compact('customer', 'tierList', 'statusList'));
    }

    public function store(CustomerRequest $request): string
    {
        Customer::create($request->validated());

        session()->flash('success', 'Data berhasil disimpan!');

        return "<script>window.location='".route('customers.index')."'</script>";
    }

    public function edit(Customer $customer): View
    {
        $tierList = Customer::tierList();
        $statusList = Customer::statusList();

        return view('pages.master_data.customers.edit', compact('customer', 'tierList', 'statusList'));
    }

    public function update(CustomerRequest $request, Customer $customer): string
    {
        $customer->update($request->validated());

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('customers.index')."'</script>";
    }

    public function destroy(Customer $customer): string
    {
        $customer->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('customers.index')."'</script>";
    }
}
