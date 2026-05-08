<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Permission;
use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\Role;
use Illuminate\Support\Facades\Session;

class PermissionController extends Controller
{
    /**
     * @OA\Post(
     *      path="/permissions/check-menu-group",
     *      summary="Check if a user has any permission for a menu group",
     *      tags={"Permissions"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="role_id", type="integer", example=1, description="ID of the user's role"),
     *              @OA\Property(property="id", type="integer", example=1, description="ID of the menu group")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Menu group permission checked successfully."),
     *              @OA\Property(property="data", type="boolean", example=true, description="True if user has permission, false otherwise")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="User not authenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function checkMenuGroup(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
            'id' => 'required|integer',
        ]);

        $role_id = $request->input('role_id');
        $id = $request->input('id');

        if (!Auth::check()) {
            return $this->sendResponse(false, 'User not authenticated.');
        }
        if (Auth::user()->user_type === 'admin') {
            return $this->sendResponse(true, 'Admin user has all permissions.');
        }

        $hasPermission = Permission::where('role_id', $role_id)
                                    ->whereHas('menu', function ($query) use ($id) {
                                        $query->where('menu_group_id', $id);
                                    })
                                    ->where(function ($query) {
                                        $query->where('can_create', true)
                                            ->orWhere('can_read', true)
                                            ->orWhere('can_update', true)
                                            ->orWhere('can_delete', true);
                                    })
                                    ->count() > 0;

        return $this->sendResponse($hasPermission, 'Menu group permission checked successfully.');
    }

    /**
     * @OA\Post(
     *      path="/permissions/check-menu",
     *      summary="Get all permissions for a menu item for the authenticated user's role",
     *      tags={"Permissions"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=5, description="ID of the menu item")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Menu permissions retrieved successfully."),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="can_read", type="boolean", example=true),
     *                  @OA\Property(property="can_create", type="boolean", example=false),
     *                  @OA\Property(property="can_update", type="boolean", example=true),
     *                  @OA\Property(property="can_delete", type="boolean", example=false)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="User not authenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function checkMenu(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        // $role_id = Session::get('role_id');
        $role_id = Auth::user()->employee->role_id;
   
        $id = $request->input('id');
        
        if (!Auth::check()) {
            return $this->sendResponse(false, 'User not authenticated.');
        }
        if (Auth::user()->user_type === 'admin') {
            return $this->sendResponse([
                'can_read' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
            ], 'Admin user has all permissions.');
        }

        $permission = Permission::where('role_id', $role_id)
                                    ->where('menu_id', $id)
                                    ->first();

        $permissions = [
                            'can_read'   => $permission ? $permission->can_read   : null,
                            'can_create' => $permission ? $permission->can_create : null,
                            'can_update' => $permission ? $permission->can_update : null,
                            'can_delete' => $permission ? $permission->can_delete : null,
                        ];


        return $this->sendResponse($permissions, 'Menu permissions retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/permissions/by-role/{role}",
     *      summary="Get all permissions for a specific role",
     *      tags={"Permissions"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="role",
     *          in="path",
     *          required=true,
     *          description="ID of the role",
     *          @OA\Schema(type="integer", format="int64", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Permission")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Role not found"
     *      )
     * )
     */
    public function getPermissionsByRole(Role $role)
    {
        $permissions = Permission::where('role_id', $role->id)->get();

        return response()->json($permissions);
    }
}