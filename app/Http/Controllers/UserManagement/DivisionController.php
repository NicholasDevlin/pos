<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\DivisionRequest;
use App\Models\UserManagement\Division;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DivisionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['role:super-admin']);
        $this->middleware(['ajax'])->only(['create', 'show', 'edit']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Renderable|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.user_management.divisions.index');
    }

    private function tableData(): Collection
    {
        return Division::orderByDesc('updated_at')
            ->get(['id', 'name', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-secondary' data-remote='true' href='".route('divisions.edit', [$datum->id])."' title='Edit' onclick='$.rails.handleRemote($(this)); return false;'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('divisions.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete' onclick='if ($.rails.allowAction($(this))) $.rails.handleRemote($(this)); return false;'><i class='feather-trash-2 text-white'></i></a>",
                ]));

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Renderable
    {
        $division = new Division;
        $statusList = Division::statusList();

        return view('pages.user_management.divisions.edit', compact('division', 'statusList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DivisionRequest $request)
    {
        Division::create($request->all());

        session()->flash('success', 'Data berhasil disimpan!');

        return "<script>window.location='".route('divisions.index')."'</script>";
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division): Renderable
    {
        $statusList = Division::statusList();

        return view('pages.user_management.divisions.edit', compact('division', 'statusList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DivisionRequest $request, Division $division)
    {
        $division->update($request->all());

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('divisions.index')."'</script>";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $division->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('divisions.index')."'</script>";
    }
}
