<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // Assuming Yajra/Laravel-DataTables is used or will be installed
use Illuminate\Support\Facades\Log;

use App\Imports\PermissionsImport;
use Maatwebsite\Excel\Facades\Excel;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function assignPermissions(Role $role)
    {
        if (checkMenu(session('role_id'), 15, 'read') == false) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $menuGroups = MenuGroup::with(['menus'])->get();

        $rolePermissions = $role->permissions()->get()->keyBy('menu_id');

        $displayPermissions = [];
        foreach ($menuGroups as $menuGroup) {
            foreach ($menuGroup->menus as $menu) {

                $permission = $rolePermissions->get($menu->id);

                if (!$permission) {
                    $permission = new Permission([
                        'role_id' => $role->id,
                        'menu_id' => $menu->id,
                        'can_create' => false,
                        'can_read' => false,
                        'can_update' => false,
                        'can_delete' => false,
                    ]);
                }

                $menu->setRelation('permission', $permission);
            }
        }

        return view('roles.assign-permissions', compact('role', 'menuGroups'));
    }

    public function savePermissions(Request $request, Role $role)
    {
        $request->validate([
            'menu_permissions' => 'nullable|array',
            'menu_permissions.*.*' => 'in:create,read,update,delete',
        ]);

        $menuIdsWithAssignedPermissions = [];

        if ($request->has('menu_permissions')) {
            foreach ($request->input('menu_permissions') as $menuId => $actions) {
                $menu = Menu::find($menuId);
                if ($menu) {
                    $menuIdsWithAssignedPermissions[] = $menu->id;

                    Permission::updateOrCreate(
                        ['role_id' => $role->id, 'menu_id' => $menu->id],
                        [
                            'can_create' => in_array('create', $actions),
                            'can_read' => in_array('read', $actions),
                            'can_update' => in_array('update', $actions),
                            'can_delete' => in_array('delete', $actions),
                        ]
                    );
                }
            }
        }

        $role->permissions()->whereNotIn('menu_id', $menuIdsWithAssignedPermissions)->delete();

        return redirect()->route('roles.index')->with('success', 'Permissions assigned successfully.');
    }

    public function togglePermission(Request $request)
    {
        if (checkMenu(session('role_id'), 15, 'update') == false) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'menu_id' => 'required|exists:menus,id',
            'action' => 'required|in:create,read,update,delete',
            'status' => 'required|boolean',
        ]);

        $role = Role::find($request->role_id);
        $menuId = $request->menu_id;
        $action = $request->action;
        $status = $request->status;

        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        $currentPermissions = Permission::where('role_id', $role->id)
            ->where('menu_id', $menuId)
            ->first();

        $attributes = [
            'role_id' => $role->id,
            'menu_id' => $menuId,
        ];

        $values = [
            'can_create' => $currentPermissions ? $currentPermissions->can_create : false,
            'can_read' => $currentPermissions ? $currentPermissions->can_read : false,
            'can_update' => $currentPermissions ? $currentPermissions->can_update : false,
            'can_delete' => $currentPermissions ? $currentPermissions->can_delete : false,
        ];

        $values['can_' . $action] = $status;

        Permission::updateOrCreate($attributes, $values);

        $message = 'Permission ' . $action . ' ' . ($status ? 'granted' : 'revoked') . ' successfully.';

        Log::info('Permission toggled', [
            'role_id' => $role->id,
            'menu_id' => $menuId,
            'action' => $action,
            'status' => $status,
        ]);

        return response()->json(['message' => $message]);
    }

    public function importPermissions(Request $request, Role $role)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new PermissionsImport($role->id), $request->file('file'));

        return back()->with('success', 'Permissions imported successfully.');
    }

    public function exportPermissionsTemplate(Role $role)
    {
        return Excel::download(new \App\Exports\PermissionsExport($role->id), 'Permissions ' . $role->role . '.xlsx');
    }
}
