<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Handle user login
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'credentials' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens for this user
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'employee_id' => $user->employee_id,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Handle user logout
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('refreshed-token')->plainTextToken;

        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'employee_id' => $user->employee_id,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get authenticated user details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'employee_id' => $user->employee_id,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
            ]
        ]);
    }
}