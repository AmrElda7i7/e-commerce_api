<?php

namespace App\Http\Controllers\Admin\Authorization;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\User;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller; // Ensure this is imported
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * @group Roles
 *
 * APIs for managing roles
 */
class RoleController extends Controller implements HasMiddleware
{
    use ApiHandlerTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_role', only: ['store']),
            new Middleware('permission:update_role', only: ['update']),
            new Middleware('permission:delete_role', only: ['destroy']),
            new Middleware('permission:update_user', only: ['assignRoleToUser']),
            new Middleware('permission:show_roles', only: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of the roles.
     *
     * @authenticated
     * @response 200 {
     *  "status": true,
     *  "message": "Roles retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "admin",
     *      "permissions": [
     *        {
     *          "id": 1,
     *          "name": "create_user"
     *        }
     *      ]
     *    }
     *  ]
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "success": false,
     *  "message": "User does not have the right permissions."
     * }
     */
    public function index()
    {
        $roles = Role::all();
        return $this->successResponse('Roles retrieved successfully', RoleResource::collection($roles));
    }

    /**
     * Store a newly created role in storage.
     *
     * @authenticated
     * @param  \App\Http\Requests\RoleRequest  $request
     * 
     * @bodyParam name string required The name of the role. Example: editor
     * @bodyParam permissions array required The permissions to be assigned to the role. Example: ["create_post", "edit_post"]
     *
     * @response 201 {
     *  "status": true,
     *  "message": "Role created successfully",
     *  "data": {
     *    "id": 2,
     *    "name": "editor",
     *    "permissions": []
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "success": false,
     *  "message": "User does not have the right permissions."
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "name": ["The name field is required."]
     *    }
     *  }
     * }
     */
    public function store(RoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('name', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return $this->successResponse('Role created successfully', new RoleResource($role), 201);
    }

    /**
     * Display the specified role.
     *
     * @authenticated
     * @param  \Spatie\Permission\Models\Role  $role
     * @response 200 {
     *  "status": true,
     *  "message": "Role retrieved successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "admin",
     *    "permissions": [
     *      {
     *        "id": 1,
     *        "name": "create_user"
     *      }
     *    ]
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "success": false,
     *  "message": "User does not have the right permissions."
     * }
     * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function show(Role $role)
    {
        return $this->successResponse('Role retrieved successfully', new RoleResource($role));
    }

    /**
     * Update the specified role in storage.
     *
     * @authenticated
     * @param  \App\Http\Requests\RoleRequest  $request
     * @param  \Spatie\Permission\Models\Role  $role
     * 
     * @bodyParam name string required The name of the role. Example: editor
     * @bodyParam permissions array required The permissions to be assigned to the role. Example: ["create_post", "edit_post"]
     *
     * @response 200 {
     *  "status": true,
     *  "message": "Role updated successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "admin",
     *    "permissions": [
     *      {
     *        "id": 1,
     *        "name": "create_user"
     *      }
     *    ]
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "success": false,
     *  "message": "User does not have the right permissions."
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "name": ["The name field is required."]
     *    }
     *  }
     * }
     *         @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function update(RoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('name', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return $this->successResponse('Role updated successfully', new RoleResource($role));
    }

    /**
     * Remove the specified role from storage.
     *
     * @authenticated
     * @param  \Spatie\Permission\Models\Role  $role
     * @response 200 {
     *  "status": true,
     *  "message": "Role deleted successfully",
     *  "data": null
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "success": false,
     *  "message": "User does not have the right permissions."
     * }
     * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return $this->successResponse('Role deleted successfully');
    }

    /**
     * @group User management
     * 
     * Assign a role to a user
     *
     * This endpoint allows assigning a role to a user.
     *
     * @authenticated
     * 
     * @bodyParam user_id int required The ID of the user. Example: 1
     * @bodyParam role_id int required The ID of the role. Example: 2
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "The role has been assigned to John Doe successfully"
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "success": false,
     *  "message": "User does not have the right permissions."
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "User or role not found"
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "An error occurred while assigning the role"
     * }
     */
    public function assignRoleToUser(AssignRoleRequest $request)
    {
        try {
            $user = User::findOrFail($request->user_id);
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);

            return $this->successResponse('The role has been assigned to ' . $user->name . ' successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('User or role not found', 404, ["User or role not found"]);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while assigning the role', 500, [$e->getMessage()]);
        }
    }
}
