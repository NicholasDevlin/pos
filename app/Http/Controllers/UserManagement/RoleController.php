<?php

namespace App\Http\Controllers\UserManagement;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\RoleRequest;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['role:super-admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Renderable|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.user_management.roles.index');
    }

    private function tableData(): Collection
    {
        return Role::orderByDesc('updated_at')
            ->get(['id', 'name', 'notes', 'created_at', 'updated_at'])
            ->setHidden(['created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->created_at_frmt = $datum->created_at->format('d-m-Y H.i.s');
                $datum->updated_at_frmt = $datum->updated_at->format('d-m-Y H.i.s');

                $datum->actions = implode(' ', array_filter([
                    "<a class='btn btn-xs btn-primary' href='".route('roles.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    "<a class='btn btn-xs btn-secondary' href='".route('roles.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('roles.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete' onclick='if ($.rails.allowAction($(this))) $.rails.handleRemote($(this)); return false;'><i class='feather-trash-2 text-white'></i></a>",
                ]));

                return $datum;
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Renderable
    {
        $role = new Role;

        $permissions = Permission::pluck('notes', 'name')
            ->groupBy(fn ($item, $key) => explode('.', $key)[0], true);

        $models = LogHelper::getAllModels();

        return view('pages.user_management.roles.edit', compact('role', 'permissions', 'models'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $input = $request->validated();

            $permissions = $input['permissions'] ?? [];
            $logScopes = $input['log_scopes'] ?? [];

            $role = Role::create($input);
            $role->syncPermissions($permissions);

            DB::table('role_has_log_scopes')
                ->insertOrIgnore(collect($logScopes)
                    ->map(fn ($logName) => [
                        'role_id' => $role->id,
                        'model_type' => LogHelper::mapLogNameToModel($logName),
                    ])
                    ->all()
                );

            DB::commit();

            return redirect()->route('roles.index')->with(['success' => 'Data berhasil disimpan!']);
        } catch (\Exception $e) {
            report($e);

            DB::rollBack();

            return redirect()->back()->withInput()->with(['fail' => 'Data tidak berhasil disimpan!']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): Renderable
    {
        $permissions = Permission::pluck('notes', 'name')
            ->groupBy(fn ($item, $key) => explode('.', $key)[0], true);

        $models = LogHelper::getAllModels();

        $hasPermissions = $role->permissions()->pluck('name')->all();

        $hasLogScopes = DB::table('role_has_log_scopes')
            ->where('role_id', $role->id)
            ->pluck('model_type')
            ->map(fn ($model) => LogHelper::mapModelToLogName($model))
            ->all();

        return view('pages.user_management.roles.show', compact('role', 'permissions', 'models', 'hasPermissions', 'hasLogScopes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Renderable
    {
        $permissions = Permission::pluck('notes', 'name')
            ->groupBy(fn ($item, $key) => explode('.', $key)[0], true);

        $models = LogHelper::getAllModels();

        $hasPermissions = $role->permissions()->pluck('name')->all();

        $hasLogScopes = DB::table('role_has_log_scopes')
            ->where('role_id', $role->id)
            ->pluck('model_type')
            ->map(fn ($model) => LogHelper::mapModelToLogName($model))
            ->all();

        return view('pages.user_management.roles.edit', compact('role', 'permissions', 'models', 'hasPermissions', 'hasLogScopes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $input = $request->validated();

            $permissions = $input['permissions'] ?? [];
            $logScopes = $input['log_scopes'] ?? [];

            $role->update($input);
            $role->syncPermissions($permissions);

            DB::table('role_has_log_scopes')
                ->where('role_id', $role->id)
                ->whereNotIn('model_type', $logScopes)
                ->delete();

            DB::table('role_has_log_scopes')
                ->insertOrIgnore(collect($logScopes)
                    ->map(fn ($logName) => [
                        'role_id' => $role->id,
                        'model_type' => LogHelper::mapLogNameToModel($logName),
                    ])
                    ->all()
                );

            DB::commit();

            return redirect()->route('roles.index')->with(['success' => 'Data berhasil di-update!']);
        } catch (\Exception $e) {
            report($e);

            DB::rollBack();

            return redirect()->back()->withInput()->with(['fail' => 'Data tidak berhasil di-update!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('roles.index')."'</script>";
    }
}
