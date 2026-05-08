<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables; // Assuming Yajra/Laravel-DataTables is used or will be installed

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Role::select('id', 'role', 'is_active');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_active', function($row){ 
                    return (bool) $row->is_active;
                })
                ->addColumn('actions', function($row){
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="#" data-bs-toggle="modal" data-bs-target="#viewRoleModal" data-id="'.$row->id.'"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="#" title="Edit" data-bs-toggle="modal" data-bs-target="#editRoleModal" data-id="'.$row->id.'"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteRoleModal" data-id="'.$row->id.'"><i class="icon-trash"></i></a></li>';
                    if(checkMenu(Session::get('role_id'), 15, 'read')){
                        $btn .= '<li class="assign-permission"><a title="Assign Permissions" href="'.route('roles.assign-permissions', $row->id).'" ><i class="fa fa-tag"></i></a></li>'; 
                    }
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('roles.index'); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
        return view('roles.create'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|unique:roles|max:255',
            'is_active' => 'boolean',
        ]);

        Role::create($request->all());

        return response()->json(['message' => 'Role created successfully.'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {

        $request->validate([
            'role' => 'required|unique:roles,role,' . $role->id . '|max:255',
            'is_active' => 'boolean',
        ]);

        $role->update($request->all());

        return response()->json(['message' => 'Role updated successfully.', 'role' => $role], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.'], 200);
    }

    /**
     * Merge duplicate roles and update related tables.
     */
    public function mergeDuplicates()
    {
        $allRoles = Role::all();
        $seen = [];
        $report = [];

        foreach ($allRoles as $role) {
            $normalized = strtolower(trim($role->role));
            
            // Normalize variants like snake_case vs space separated vs typo
            $normalizationMap = [
                'serivce engineer' => 'service_engineer',
                'service engineer' => 'service_engineer',
                'service manager' => 'service_manager',
            ];

            if (isset($normalizationMap[$normalized])) {
                $normalized = $normalizationMap[$normalized];
            }

            if (isset($seen[$normalized])) {
                $mainRole = $seen[$normalized];
                $duplicateRole = $role;
                
                if ($mainRole->id == $duplicateRole->id) continue;

                \Illuminate\Support\Facades\DB::transaction(function () use ($mainRole, $duplicateRole, &$report) {
                    \Illuminate\Support\Facades\DB::table('employees')->where('role_id', $duplicateRole->id)->update(['role_id' => $mainRole->id]);
                    \Illuminate\Support\Facades\DB::table('user_roles')->where('role_id', $duplicateRole->id)->update(['role_id' => $mainRole->id]);
                    
                    $duplicatePermissions = \App\Models\Permission::where('role_id', $duplicateRole->id)->get();
                    foreach ($duplicatePermissions as $perm) {
                        $exists = \App\Models\Permission::where('role_id', $mainRole->id)->where('menu_id', $perm->menu_id)->exists();
                        if (!$exists) {
                            $perm->role_id = $mainRole->id;
                            $perm->save();
                        } else {
                            $perm->delete();
                        }
                    }
                    
                    $duplicateRole->delete();
                    $report[] = "Merged '{$duplicateRole->role}' (ID {$duplicateRole->id}) into '{$mainRole->role}' (ID {$mainRole->id})";
                });
            } else {
                $seen[$normalized] = $role;
            }
        }

        return response()->json([
            'status' => 'success',
            'report' => $report,
            'message' => empty($report) ? 'No duplicates found.' : 'Duplicates merged successfully.'
        ]);
    }
}
