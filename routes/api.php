<?php

use App\Http\Controllers\AuthenticationController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'create']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', [AuthenticationController::class, 'show']);
    Route::post('logout', [AuthenticationController::class, 'logout']);
    Route::post('user/reset-password', [AuthenticationController::class, 'password']);
    // TODO
    // post forgot-password
    // post reset-password with {token}
    // get /verify-email/{id}/{hash}
});

