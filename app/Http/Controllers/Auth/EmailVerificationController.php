<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Traits\ApiHandlerTrait;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\EmailVerificationRequest ;
use App\Http\Controllers\Controller ;

/**
 * @group Auth
 * APIs for email verification
 */
class EmailVerificationController extends Controller
{
    use ApiHandlerTrait;

    private $otp;

    public function __construct()
    {
        $this->otp = new Otp();
    }

    /**
     * Verify Email
     *
     * @authenticated
     * 
     * This endpoint verifies the email of the user using the provided OTP.
     *
     * @bodyParam email string required The email address of the user. Example: johndoe@example.com
     * @bodyParam otp string required The OTP sent to the user's email. Example: 123456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Your email has been verified successfully.",
     *   "data": null
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid OTP",
     *   "errors": {
     *     "otp": "The provided OTP is invalid."
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "User not found",
     *   "errors": null
     * }
     *
     * @param EmailVerificationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function email_verification(EmailVerificationRequest $request)
    {
        // Validate OTP
        $otpValidation = $this->otp->validate($request->email, $request->otp);

        if (!$otpValidation->status) {
            return $this->errorResponse('Invalid OTP', 401, ['otp' => $otpValidation->message]);
        }

        // Find user by email and update verification status
        $user = DB::table('users')->where('email', $request->email)->first();
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        DB::table('users')->where('email', $request->email)->update(['is_verified' => true]);

        return $this->successResponse('Your email has been verified successfully.', null, 200);
    }
}
