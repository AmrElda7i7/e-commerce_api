<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Http\Resources\AuthUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller ;

/**
 * @group User management
 *
 * APIs for managing users.
 */
class UserController extends Controller implements HasMiddleware
{
    use ApiHandlerTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_user', only: ['store']),
            new Middleware('permission:update_user', only: ['update']),
            new Middleware('permission:delete_user', only: ['destroy']),
            new Middleware('permission:show_users', only: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the users.
     *
     * @authenticated
     * @response 200 {
     *  "status": true,
     *  "message": "Users retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john.doe@example.com",
     *      "role": "admin"
     *    }
     *  ]
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     */
    public function index()
    {
        $users = User::where('id','!=' ,auth()->user()->id)->get();
        return $this->successResponse('Users retrieved successfully', UserResource::collection($users));
    }

    /**
     * Store a newly created user in storage.
     *
     * @authenticated
     * @param  \App\Http\Requests\UserRequest  $request
     * @response 201 {
     *  "status": true,
     *  "message": "User created successfully",
     *  "data": {
     *    "id": 2,
     *    "name": "Jane Doe",
     *    "email": "jane.doe@example.com",
     *    "role": "editor"
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "email": ["The email has already been taken."]
     *    }
     *  }
     * }
     */
    public function store(UserRequest $request)
    {
        $user = null;
        DB::transaction(function() use($request , &$user){

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);
            $user->notify(new EmailVerificationNotification());
        }) ;
        $user = new UserResource($user);
        return $this->successResponse("user created successfully they should verify their email", $user , 201);
    }

    /**
     * Display the specified user.
     *
     * @authenticated
     * @param  int  $id
     * @response 200 {
     *  "status": true,
     *  "message": "User retrieved successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john.doe@example.com",
     *    "role": "admin"
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
        * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->successResponse('User retrieved successfully', new UserResource($user));
    }

    /**
     * Update the specified user in storage.
     *
     * @authenticated
     * @param  \App\Http\Requests\UserRequest  $request
     * @param  \App\Models\User  $user
     * @response 200 {
     *  "status": true,
     *  "message": "User updated successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john.doe@example.com",
     *    "role": "admin"
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
           * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "email": ["The email has already been taken."]
     *    }
     *  }
     * }
     */
    public function update(Request $request, $id)
    {
        $user = null;
        DB::transaction(function() use($request , &$user ,$id){

            $user = User::findOrFail($id) ;
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);
    
            $role = Role::findOrFail($request->role_id);
            $user->syncRoles($role);
        }) ;
        $user = new UserResource($user);
        

        return $this->successResponse('User updated successfully', new UserResource($user));
    }

    /**
     * Remove the specified user from storage.
     *
     * @authenticated
     * @param  \App\Models\User  $user
     * @response 200 {
     *  "status": true,
     *  "message": "User deleted successfully",
     *  "data": null
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
            * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id) ;
        $user->delete();
        return $this->successResponse('User deleted successfully');
    }
}
