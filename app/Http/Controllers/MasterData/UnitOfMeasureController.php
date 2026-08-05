<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\UnitOfMeasureRequest;
use App\Models\MasterData\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UnitOfMeasureController extends Controller
{
    public function __construct()
    {
        $this->middleware(['ajax'])->only(['create', 'show', 'edit']);
        $this->middleware(['permission:units_of_measure.show'])->only(['index', 'show']);
    }

    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.master_data.units_of_measure.index');
    }

    private function tableData(): Collection
    {
        return UnitOfMeasure::orderByDesc('updated_at')
            ->get(['id', 'code', 'name', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-secondary' data-remote='true' href='".route('units-of-measure.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('units-of-measure.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>",
                ]));

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    public function create(): View
    {
        $unitsOfMeasure = new UnitOfMeasure;
        $statusList = UnitOfMeasure::statusList();

        return view('pages.master_data.units_of_measure.edit', compact('unitsOfMeasure', 'statusList'));
    }

    public function store(UnitOfMeasureRequest $request): string
    {
        UnitOfMeasure::create($request->validated());

        session()->flash('success', 'Data berhasil disimpan!');

        return "<script>window.location='".route('units-of-measure.index')."'</script>";
    }

    public function edit(UnitOfMeasure $unitsOfMeasure): View
    {
        $statusList = UnitOfMeasure::statusList();

        return view('pages.master_data.units_of_measure.edit', compact('unitsOfMeasure', 'statusList'));
    }

    public function update(UnitOfMeasureRequest $request, UnitOfMeasure $unitsOfMeasure): string
    {
        $unitsOfMeasure->update($request->validated());

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('units-of-measure.index')."'</script>";
    }

    public function destroy(UnitOfMeasure $unitsOfMeasure): string
    {
        $unitsOfMeasure->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('units-of-measure.index')."'</script>";
    }
}
