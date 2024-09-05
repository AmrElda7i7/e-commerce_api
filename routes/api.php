<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\IsVerified;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Authorization\RoleController ;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('email-verification',[EmailVerificationController::class,'email_verification']);
});
Route::middleware(['auth:sanctum' ,IsVerified::class])->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::post('assign-role' , [RoleController::class ,'assignRoleToUser']) ;
    Route::apiResource('users', UserController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::post('orders' ,[OrderController::class,'store' ]) ;
    Route::get('callback'  , [OrderController::class , 'callback']) ;
    Route::get('orders' , [App\Http\Controllers\Admin\OrderController::class ,'index']) ;
    Route::get('orders/{id}' , [App\Http\Controllers\Admin\OrderController::class ,'show']) ;
    Route::get('orders-for-user' ,[OrderController::class , 'getOrdersForUser']) ;
    Route::get('get-review-for-product' ,[ReviewController::class,'getReviewForProduct' ]) ;
    Route::delete('reviews/{id}' ,[App\Http\Controllers\Admin\ReviewController::class ,'destroy']) ;
    Route::get('reviews' ,[App\Http\Controllers\Admin\ReviewController::class ,'index']) ;
    Route::get('reviews/{id}' ,[App\Http\Controllers\Admin\ReviewController::class ,'show']) ;
    Route::post('reviews' ,[ReviewController::class,'store' ]) ;
 Route::get('orders' , [App\Http\Controllers\Admin\OrderController::class ,'index']) ;
    // Route::get('all-products'  )
});
Route::post('forget-password' , [ForgetPasswordController::class ,'forgetPassword']) ;
Route::post('reset-password' , [ResetPasswordController::class ,'resetPassword']) ;
Route::post('login',[AuthController::class,'login']);
Route::post('register',[AuthController::class,'register']);
Route::get('callback'  , [OrderController::class , 'callback']) ;
