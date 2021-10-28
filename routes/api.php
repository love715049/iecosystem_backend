<?php

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SocialiteController;
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

Route::get('test/email', [AuthenticationController::class, 'email']);

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'create']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', [AuthenticationController::class, 'show']);
    Route::post('logout', [AuthenticationController::class, 'logout']);
    Route::post('user/reset-password', [AuthenticationController::class, 'password']);
});

Route::middleware(['auth:sanctum'])->prefix('messages')->group(function () {
    Route::get('', [MessageController::class, 'index']);
    Route::post('', [MessageController::class, 'create']);
    Route::post('storage', [MessageController::class, 'create_storage']);
});

Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->name('verification.verify'); // Email 驗證連結

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');// Reset password 連結

Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->name('password.update');

//facebook
Route::get('/facebook/auth', [SocialiteController::class, 'login']);
Route::get('/facebook/authCallback', [SocialiteController::class, 'call_back']);
