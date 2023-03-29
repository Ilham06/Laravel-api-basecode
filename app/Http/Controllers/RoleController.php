<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Create role
     * @OA/Post(
     *      path="/api/role/create"
     *      tags={"Role"}
     *      @OA/RequestBody(
     *          @OA/MediaType(
     *              mediaType="json/application"
     *              @OA/Schema(
     *                  @OA/Property(
     *                      @OA/Property(
     *                          property="name"
     *                          type="string"
     *                          example="moderator"
     *                      ),
     *                      @OA/Permissions(
     *                          type="array",
     *                          @OA/Permissionss(type="string", example="'edit','create'")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA/Response(
     *          response=200,
     *          description="Success",
     *          @OA/JsonContent(
     *              @OA\Property(property="id", type="number", example=1),
     *              @OA\Property(property="name", type="string", example="name"),
     *          )
     *      )
     * )
     */
    public function create(Request $request) {
        DB::beginTransaction();
        $role = Role::create([
            'name' => $request->role
        ]);
        foreach ($request->permissions as $key => $value) {
            $permission = Permission::create([
                'name' => $value
            ]);
            $role->givePermissionTo($permission);
        }

        DB::commit();
        return response()->json($role);

    }
}
