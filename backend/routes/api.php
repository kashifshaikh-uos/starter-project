<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PrivilegeGroupController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/app-config', function () {
    return response()->json([
        'name'          => config('site.name'),
        'short_name'    => config('site.short_name'),
        'icon'          => config('site.icon'),
        'primary_color' => config('site.primary_color'),
    ]);
});

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

// Protected
Route::middleware(['auth:api', 'privilege'])->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/change-role', [AuthController::class, 'changeRole']);

    // Users
    Route::apiResource('users', UserController::class)->except(['destroy']);
    Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles']);

    // Roles
    Route::apiResource('roles', RoleController::class)->except(['destroy']);
    Route::post('roles/{role}/assign-privileges', [RoleController::class, 'assignPrivileges']);

    // Privilege Groups
    Route::apiResource('privilege-groups', PrivilegeGroupController::class)->except(['destroy']);

    // Privileges
    Route::apiResource('privileges', PrivilegeController::class)->except(['destroy']);
});
