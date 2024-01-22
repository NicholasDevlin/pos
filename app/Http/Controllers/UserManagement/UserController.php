<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\UserRequest;
use App\Models\User;
use App\Models\UserManagement\Division;
use App\Models\UserManagement\Location;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
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

        return view('pages.user_management.users.index');
    }

    private function tableData(): Collection
    {
        return User::orderByDesc('updated_at')
            ->get(['id', 'name', 'username', 'status', 'created_at', 'updated_at'])
            ->map(function ($datum) {
                $datum->actions = implode(' ', [
                    "<a class='btn btn-xs btn-primary' href='".route('users.show', [$datum->id])."' title='Show'><i class='feather-eye text-white'></i></a>",
                    "<a class='btn btn-xs btn-secondary' href='".route('users.edit', [$datum->id])."' title='Edit'><i class='feather-edit-2 text-white'></i></a>",
                    "<a class='btn btn-xs btn-danger' data-remote='true' href='".route('users.destroy', [$datum->id])."' data-params='{&quot;_token&quot;:&quot;".csrf_token()."&quot;}' data-method='delete' data-confirm='Apakah Anda yakin akan menghapus data ini?' title='Delete' onclick='if ($.rails.allowAction($(this))) $.rails.handleRemote($(this)); return false;'><i class='feather-trash-2 text-white'></i></a>",
                ]);

                $datum->status = $datum->statusLabel();

                return $datum;
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Renderable
    {
        $user = new User;

        $statusList = User::statusList();

        $roles = Role::pluck('notes', 'name')->all();
        $locations = Location::where('status', Location::STATUS_ACTIVE)->pluck('name', 'id')->all();
        $divisions = Division::where('status', Division::STATUS_ACTIVE)->pluck('name', 'id')->all();

        return view('pages.user_management.users.edit', compact('user', 'statusList', 'roles', 'locations', 'divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $roles = $input['roles'] ?? [];
            $scopes = $input['scopes'] ?? [];
            $userScopes = [];

            $user = User::create($input);
            $user->syncRoles($roles);

            foreach ($scopes as $location => $divisions) {
                foreach ($divisions as $division) {
                    $userScopes[] = [
                        'model_type' => get_class($user),
                        'model_id' => $user->id,
                        'location_id' => $location,
                        'division_id' => $division,
                    ];
                }
            }

            DB::table('model_has_scopes')
                ->insertOrIgnore($userScopes);

            DB::commit();

            return redirect()->route('users.index')->with(['success' => 'Data berhasil disimpan!']);
        } catch (\Exception $e) {
            report($e);

            DB::rollBack();

            return redirect()->back()->withInput()->with(['fail' => 'Data tidak berhasil disimpan!']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Renderable
    {
        $roles = Role::pluck('notes', 'name')->all();
        $locations = Location::where('status', Location::STATUS_ACTIVE)->pluck('name', 'id')->all();
        $divisions = Division::where('status', Division::STATUS_ACTIVE)->pluck('name', 'id')->all();

        $hasRoles = $user->roles()->pluck('name')->all();

        $hasScopes = $user->modelHasScopes()
            ->get()
            ->map(fn ($scope) => "[{$scope['location_id']}][{$scope['division_id']}]")
            ->all();

        return view('pages.user_management.users.show', compact('user', 'roles', 'locations', 'divisions', 'hasRoles', 'hasScopes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Renderable
    {
        $statusList = User::statusList();

        $roles = Role::pluck('notes', 'name')->all();
        $locations = Location::where('status', Location::STATUS_ACTIVE)->pluck('name', 'id')->all();
        $divisions = Division::where('status', Division::STATUS_ACTIVE)->pluck('name', 'id')->all();

        $hasRoles = $user->roles()->pluck('name')->all();

        $hasScopes = $user->modelHasScopes()
            ->get()
            ->map(fn ($scope) => "[{$scope['location_id']}][{$scope['division_id']}]")
            ->all();

        return view('pages.user_management.users.edit', compact('user', 'statusList', 'roles', 'locations', 'divisions', 'hasRoles', 'hasScopes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $input = $request->except(['username']);
            $input['password'] = ! empty($input['password']) ? Hash::make($input['password']) : $user->password;

            $roles = $input['roles'] ?? [];
            $scopes = $input['scopes'] ?? [];
            $userScopes = [];

            $user->update($input);
            $user->syncRoles($roles);

            $scopesWhereQuery = [];

            foreach ($scopes as $location => $divisions) {
                foreach ($divisions as $division) {
                    $scopesWhereQuery[] = "($location,$division)";

                    $userScopes[] = [
                        'model_type' => get_class($user),
                        'model_id' => $user->id,
                        'location_id' => $location,
                        'division_id' => $division,
                    ];
                }
            }

            $scopesWhereRawQuery = implode(',', $scopesWhereQuery) ?: "('','')";

            DB::table('model_has_scopes')
                ->where([
                    'model_type' => get_class($user),
                    'model_id' => $user->id,
                ])
                ->whereRaw("(location_id,division_id) NOT IN ($scopesWhereRawQuery)")
                ->delete();

            DB::table('model_has_scopes')
                ->insertOrIgnore($userScopes);

            DB::commit();

            return redirect()->route('users.index')->with(['success' => 'Data berhasil di-update!']);
        } catch (\Exception $e) {
            report($e);

            DB::rollBack();

            return redirect()->back()->withInput()->with(['fail' => 'Data tidak berhasil di-update!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        session()->flash('success', 'Data berhasil dihapus!');

        return "<script>window.location='".route('users.index')."'</script>";
    }
}
