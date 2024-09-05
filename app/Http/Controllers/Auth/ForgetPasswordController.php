<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ForgetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;

/**
 * @group Auth
 *
 * APIs for authentication and password reset
 */
class ForgetPasswordController extends Controller
{
    use ApiHandlerTrait;

    /**
     * Send Password Reset Notification
     *
     * This endpoint allows a user to request a password reset email.
     *
     * @bodyParam email string required The email of the user. Example: user@example.com
     *
     * @response 200 {
     *  "status": true,
     *  "message": "password reset message has been sent",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "status": false,
     *  "message": "User not found",
     *  "data": null
     * }
     */
    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $email = $request->only(['email']);
        $user = User::where('email', $email)->firstOrFail();
        $user->notify(new ResetPasswordNotification());
        return $this->successResponse('password reset message has been sent');
    }
}
