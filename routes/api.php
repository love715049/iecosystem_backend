<?php

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
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

Route::post('login', [AuthenticationController::class, 'login']);
Route::post('register', [AuthenticationController::class, 'create']);
Route::post('logout', [AuthenticationController::class, 'logout'])->middleware(['auth:sanctum']);

Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::get('', [AuthenticationController::class, 'show']);
    Route::post('profile', [AuthenticationController::class, 'profile']);
    Route::post('reset-password', [AuthenticationController::class, 'password']);
});

Route::prefix('orders')->group(function () {
    Route::get('types', [OrderController::class, 'types']);
});
Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::post('', [OrderController::class, 'index']);
    Route::post('{order}/close', [OrderController::class, 'close']);
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

// provider login
Route::get('line/auth', [SocialiteController::class, 'line_login']);
Route::get('line/authCallback', [SocialiteController::class, 'line_call_back']);
Route::get('{provider}/auth', [SocialiteController::class, 'login']);
Route::get('{provider}/authCallback', [SocialiteController::class, 'call_back']);

//admin
Route::middleware(['auth:sanctum'])->prefix('users')->group(function () {
    Route::get('', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::get('{user}/messages', [\App\Http\Controllers\Admin\MessageController::class, 'index']);
    Route::post('{user}/messages', [\App\Http\Controllers\Admin\MessageController::class, 'create']);
});
Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::get('assign', [\App\Http\Controllers\Admin\OrderController::class, 'index']);
    Route::get('{order}/messages', [\App\Http\Controllers\Admin\OrderController::class, 'messages']);
    Route::post('{order}/assign', [\App\Http\Controllers\Admin\OrderController::class, 'assign']);
});
