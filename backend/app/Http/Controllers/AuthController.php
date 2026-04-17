<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'cnic_no' => 'required|string|size:13',
            'password' => 'required|string',
        ]);

        $user = User::where('cnic_no', $request->cnic_no)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid CNIC or password'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated'], 403);
        }

        // Auto-set active role if not set or invalid
        $activeRoleIds = $user->activeRoles()->pluck('roles.id');
        if (!$user->active_role_id || !$activeRoleIds->contains($user->active_role_id)) {
            $user->update(['active_role_id' => $activeRoleIds->first()]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user->load('activeRole', 'activeRoles'),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('activeRole', 'activeRoles');

        return response()->json([
            'user' => $user,
            'menu' => $user->getMenuItems(),
            'privileges' => $user->getAllPrivileges()->pluck('slug'),
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:8', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => $request->password]);

        // Invalidate current token so user must re-login
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function changeRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = $request->user();

        // Verify user has this role assigned and active
        $hasRole = $user->activeRoles()
            ->where('roles.id', $request->role_id)
            ->where('roles.is_active', true)
            ->exists();

        if (!$hasRole) {
            return response()->json(['message' => 'You do not have access to this role.'], 403);
        }

        $user->update(['active_role_id' => $request->role_id]);
        $user->load('activeRole', 'activeRoles');

        return response()->json([
            'message' => 'Role switched successfully',
            'user' => $user,
            'menu' => $user->getMenuItems(),
            'privileges' => $user->getAllPrivileges()->pluck('slug'),
        ]);
    }
}
