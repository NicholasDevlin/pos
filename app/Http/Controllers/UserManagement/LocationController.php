<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\LocationRequest;
use App\Models\UserManagement\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LocationController extends Controller
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
    public function index(Request $request): View|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.user_management.locations.index');
    }

    private function tableData(): Collection
    {
        return Location::orderByDesc('updated_at')
            ->get(['id', 'name', 'real_name', 'short_name', 'code_name', 'notes', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-secondary' data-remote='true' href='".route('locations.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('locations.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete'><i class='feather-trash-2 text-white'></i></a>",
                ]));

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $location = new Location;
        $statusList = Location::statusList();

        return view('pages.user_management.locations.edit', compact('location', 'statusList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocationRequest $request)
    {
        Location::create($request->validated());

        session()->flash('success', 'Data berhasil disimpan!');

        return "<script>window.location='".route('locations.index')."'</script>";
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location): View
    {
        $statusList = Location::statusList();

        return view('pages.user_management.locations.edit', compact('location', 'statusList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocationRequest $request, Location $location)
    {
        $location->update($request->validated());

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('locations.index')."'</script>";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('locations.index')."'</script>";
    }
}
