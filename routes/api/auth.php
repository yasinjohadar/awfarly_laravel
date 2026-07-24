<?php

use App\Http\Controllers\API\Auth\ForgetPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*************************************************************************************************************/

// Register
Route::post('register', [RegisterController::class, 'register'])
    ->middleware([
        'guest',
    ]);

// Login
Route::post('login', [LoginController::class, 'login']);

// Check
Route::get('check', [LoginController::class, 'check'])
    ->middleware([
        'auth:customer-api,advertiser-api',
    ]);

Route::post('update_token', [LoginController::class, 'update_token'])
    ->middleware([
        'auth:customer-api,advertiser-api',
    ]);

// // verify otp
// Route::post('verify/otp', [RegisterController::class, 'verify'])
//     ->middleware([
//         'guest',
//     ]);


// Logout
Route::post('logout', [LoginController::class, 'logout'])
    ->middleware([
        'auth:customer-api,advertiser-api',
    ]);

// Password Reset
Route::post('password/forget', [ForgetPasswordController::class, 'forgetPassword']);
