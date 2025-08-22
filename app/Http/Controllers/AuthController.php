<?php

namespace App\Http\Controllers;

use App\Models\Login;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'phone' => $fields['phone'],
            'password' => Hash::make($fields['password']),
        ]);

        // Generate Sanctum token (fresh token)
        $plainToken = $user->createToken('assister_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $plainToken,
        ], 201);
    }

    /**
     * Login user (with email OR phone)
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'login' => 'required|string', // email or phone
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['login'])
                    ->orWhere('phone', $fields['login'])
                    ->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Store login info
        Login::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Generate fresh Sanctum token on login
        $token = $user->createToken('assister_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout(Request $request)
    {
        // Delete the token used for the current request to invalidate it
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
