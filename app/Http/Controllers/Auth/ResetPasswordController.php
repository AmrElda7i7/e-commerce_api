<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Traits\ApiHandlerTrait;
use Illuminate\Support\Facades\DB;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

/**
 * @group Auth
 *
 * APIs for authentication and password reset
 */
class ResetPasswordController extends Controller
{
    use ApiHandlerTrait;
    
    private Otp $otp;

    public function __construct()
    {
        $this->otp = new Otp();
    }

    /**
     * Reset Password
     *
     * This endpoint allows a user to reset their password using an OTP.
     *
     * 
     * @bodyParam email string required The email of the user. Example: user@example.com
     * @bodyParam password string required The new password for the user. Example: newpassword123
     * @bodyParam otp integer required The OTP sent to the user's email. Example: 123456
     *
     * @response 200 {
     *  "status": true,
     *  "message": "Password has been updated",
     *  "data": null
     * }
     *
     * @response 401 {
     *  "status": false,
     *  "message": "Invalid OTP",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "status": false,
     *  "message": "User not found",
     *  "data": null
     * }
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Validate OTP
        $otpValidation = $this->otp->validate($request->email, $request->otp);
        
        if (!$otpValidation->status) {
            return $this->errorResponse('Invalid OTP', 401);
        }

        // Find user by email
        $user = User::where('email', $request->email)->firstOrFail();

        // Update password
        DB::transaction(function () use ($user, $request) {
            $user->update(['password' => Hash::make($request->password)]);
    
            // Revoke all tokens
            $user->tokens()->delete(); // Correct way to delete all tokens
        });

        return $this->successResponse('Password has been updated');
    }
}
