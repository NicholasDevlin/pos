<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\PermissionRequest;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['role:super-admin']);
        $this->middleware(['ajax'])->only(['create', 'edit']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Renderable|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.user_management.permissions.index');
    }

    private function tableData(): Collection
    {
        return Permission::orderByDesc('updated_at')
            ->get(['id', 'name', 'notes', 'created_at', 'updated_at'])
            ->setHidden(['created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->created_at_frmt = $datum->created_at->format('d-m-Y H.i.s');
                $datum->updated_at_frmt = $datum->updated_at->format('d-m-Y H.i.s');

                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('permissions.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    "<a class='btn btn-xs btn-secondary' data-remote='true' href='".route('permissions.edit', [$datum->id])."' title='Edit' onclick='$.rails.handleRemote($(this)); return false;'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('permissions.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete' onclick='if ($.rails.allowAction($(this))) $.rails.handleRemote($(this)); return false;'><i class='feather-trash-2 text-white'></i></a>",
                ]));

                return $datum;
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Renderable
    {
        $permission = new Permission;

        return view('pages.user_management.permissions.edit', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionRequest $request)
    {
        Permission::create($request->validated());

        session()->flash('success', 'Data berhasil disimpan!');

        return "<script>window.location='".route('permissions.index')."'</script>";
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): Renderable
    {
        $permission = Permission::with('roles:id,name,notes', 'roles.users:username,name')->find($permission->id);

        return view('pages.user_management.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission): Renderable
    {
        return view('pages.user_management.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        session()->flash('success', 'Data berhasil di-update!');

        return "<script>window.location='".route('permissions.index')."'</script>";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('permissions.index')."'</script>";
    }
}
