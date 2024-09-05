<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthUserResource;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use App\Traits\ApiHandlerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ApiHandlerTrait, HasApiTokens, HasFactory, Notifiable;

    /**
     * @group Auth
     * 
     * Register a new user
     *
     * This endpoint allows a user to register for an account. After registration,
     * a verification email will be sent to the provided email address.
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: johndoe@example.com
     * @bodyParam password string required The password of the user. Example: secret
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Please check your email to verify your account",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "johndoe@example.com",
     *       "is_verified": false,
     *       "token": "your_generated_token_here",
     *       "role": "user"
     *     }
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "Validation error",
     *   "errors": {
     *     "email": [
     *       "The email has already been taken."
     *     ]
     *   }
     * }
     */
    public function register(RegisterRequest $request)
    {
        $user = null;

        DB::transaction(function() use ($request, &$user) 
        {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create token
            $token = $user->createToken("personal access token")->plainTextToken;
            $user->token = $token;
            $role = Role::where('name', 'user')->first();
            if($role != null) 
            {
                $user->assignRole($role->name);
            }
            $user->notify(new EmailVerificationNotification());
        });

        $user = new AuthUserResource($user);
        return $this->successResponse('Please check your email to verify your account', ['user' => $user], 201);
    }

    /**
     * @group Auth
     * 
     * Login a user
     *
     * This endpoint allows a registered user to log in and receive an access token.
     *
     * @bodyParam email string required The email of the user. Example: johndoe@example.com
     * @bodyParam password string required The password of the user. Example: secret
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "You have logged in successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "johndoe@example.com",
     *       "is_verified": false,
     *       "token": "your_generated_token_here",
     *       "role": "user"
     *     }
     *   }
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "These credentials do not match our records."
     * }
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::where("email", $request->email)->firstOrFail();
            $token = $user->createToken("personal access token")->plainTextToken;
            $user->token = $token;
            $user = new AuthUserResource($user);
            return $this->successResponse('You have logged in successfully', ['user' => $user], 200);
        }

        return $this->errorResponse('These credentials do not match our records.', 401);
    }

    /**
     * @group Auth
     * 
     * Logout a user
     *
     * This endpoint allows a logged-in user to log out and revoke the access token.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "You have been successfully logged out!"
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "Something went wrong"
     * }
     */
    public function logout(Request $request)
    {
        if ($request->user()->currentAccessToken()->delete()) {
            return $this->successResponse('You have been successfully logged out!', null, 200);
        }

        return $this->errorResponse('Something went wrong', 401);
    }

   
}