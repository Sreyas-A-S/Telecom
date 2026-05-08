<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Permission",
 *     description="Permission model",
 *     @OA\Xml(name="Permission"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the permission",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="role_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the role associated with the permission",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="menu_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the menu associated with the permission",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="can_create",
 *         type="boolean",
 *         description="Indicates if the role can create",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="can_read",
 *         type="boolean",
 *         description="Indicates if the role can read",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="can_update",
 *         type="boolean",
 *         description="Indicates if the role can update",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="can_delete",
 *         type="boolean",
 *         description="Indicates if the role can delete",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time of creation",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time of last update",
 *         readOnly=true
 *     )
 * )
 */
class Permission extends Model
{
    protected $fillable = ['id', 'role_id', 'menu_id', 'can_create', 'can_read', 'can_update', 'can_delete'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}