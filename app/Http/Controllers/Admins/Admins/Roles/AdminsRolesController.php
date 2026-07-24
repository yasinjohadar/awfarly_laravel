<?php

namespace App\Http\Controllers\Admins\Admins\Roles;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use App\Models\Users\Admins\Groups\Group;
use App\Models\Users\Admins\Groups\Permissions\GroupPermission;
use Auth;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AdminsRolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('admins.roles.inquiry')) {
            return abort(404);
        }
        //Log Action
        AdminLogs::log('inquiry', 'Roles');

        return view('admin.pages.admins.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('admins.roles.add')) {
            return abort(404);
        }

        //Get the groups with permissions within it
        $groups = Group::with([
            'permissions' => function ($query) {
                $query->where('permissions_groups_data.is_allowed', true)
                    ->where('permissions_groups_data.is_active', true);
            }
        ])
            ->where('permissions_groups.is_allowed', true)
            ->where('permissions_groups.is_active', true)
            ->get()
            ->map(function ($group) {
                return [
                    'groupName' => __("pages/admins/roles/roles.names.$group->name"),
                    'groupData' => $group->permissions->map(function ($permission) {
                        return [
                            'permission' => __("pages/admins/roles/roles.keys.$permission->name"),
                            'value' => $permission->key,
                        ];
                    }),
                ];
            });
        return view('admin.pages.admins.roles.create', ['groups' => $groups]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse|void
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        if (!Auth::guard('admin')->user()->can('admins.roles.add')) {
            return abort(404);
        }

        $this->validate($request, [
            'name' => ['required', 'unique:roles,name'],
            'permissions' => ['required', 'exists:permissions,name'],
        ]);

        DB::beginTransaction();
        try {
            //Create role
            $role = Role::create([
                'name' => $request->get('name'),
                'guard_name' => 'admin'
            ]);

            //Get allowed and active permissions of a set of the given permissions (to disallow the user to bypass roles)
            $permissions = GroupPermission::whereIn('key', $request->get('permissions'))
                ->where('is_allowed', true)
                ->where('is_active', true)
                ->pluck('key')
                ->toArray();

            //Set role permissions
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }

            //Log role
            AdminLogs::log('add', 'roles', [
                'name' => $request->get('name'),
                'permissions' => $permissions
            ], "Add: new Role and permissions");
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'Something went wrong!']);
        }
        DB::commit();
        return redirect()->back()->with(['success' => 'Role has been added successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return void
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Application|Factory|View|void
     */
    public function edit(int $id)
    {
        if (!Auth::guard('admin')->user()->can('admins.roles.edit')) {
            return abort(404);
        }

        //Get role
        $role = Role::findById($id);

        //Get permissions
        $permissions = $role->getAllPermissions()
            ->pluck('name')
            ->toArray();

        //Get the groups with permissions within it
        $groups = Group::with([
            'permissions' => function ($query) {
                $query->where('permissions_groups_data.is_allowed', true)
                    ->where('permissions_groups_data.is_active', true);
            }
        ])
            ->where('permissions_groups.is_allowed', true)
            ->where('permissions_groups.is_active', true)
            ->get()
            ->map(function ($group) use ($permissions) {
                return [
                    'groupName' => __("pages/admins/roles/roles.names.$group->name"),
                    'groupData' => $group->permissions->map(function ($permission) use ($permissions) {
                        return [
                            'permission' => __("pages/admins/roles/roles.keys.$permission->name"),
                            'value' => $permission->key,
                            "selected" => in_array($permission->key, $permissions),
                        ];
                    }),
                ];
            })
            ->toArray();
        return view('admin.pages.admins.roles.edit', [
            'role' => $role,
            'groups' => $groups
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|void
     * @throws ValidationException
     */
    public function update(Request $request, int $id)
    {
        if (!Auth::guard('admin')->user()->can('admins.roles.edit')) {
            return abort(404);
        }

        $this->validate($request, [
            'name' => ['required', "unique:roles,name,$id"],
            'permissions' => ['required', 'exists:permissions,name'],
        ]);

        //Get allowed and active permissions of a set of the given permissions (to disallow the user to bypass roles)
        $permissions = GroupPermission::whereIn('key', $request->get('permissions'))
            ->where('is_allowed', true)
            ->where('is_active', true)
            ->pluck('key')
            ->toArray();

        //Get role by id
        $role = Role::findById($id);

        //Get the old permissions for the logs
        $old_permissions = $role->getALlPermissions();

        DB::beginTransaction();
        try {
            //Update role name
            $role->update([
                'name' => $request->get('name'),
            ]);

            //Get all the permissions to be excluded
            $unneeded_permissions = $role->getAllPermissions()
                ->whereNotIn('name', $permissions)
                ->pluck('name')
                ->toArray();

            //Revoke Permission
            foreach ($unneeded_permissions as $unneeded_permission) {
                //exclude permission
                $role->revokePermissionTo($unneeded_permission);
            }

            //Give the permission if not already taken
            foreach ($permissions as $permission) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }

            //Get the new permissions for the log
            $new_permissions = $role->getALlPermissions();

            //Add logs
            AdminLogs::log(
                'edit',
                'roles',
                [
                    'old' => $old_permissions,
                    'new' => $new_permissions,
                ],
                "Edit: role #$id"
            );
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'Something went wrong!']);
        }
        DB::commit();

        return redirect()->back()->with(['success' => 'Role has been updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy(int $id)
    {
        //
    }
}
