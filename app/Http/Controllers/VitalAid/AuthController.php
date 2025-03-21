<?php

namespace App\Http\Controllers\VitalAid;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate user input
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'phone_number' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
                '_tag' => 'required|regex:/^[a-zA-Z0-9_]{3,30}$/|unique:users,_tag',
                'password' => 'required|string|min:6',
                'role' => 'required|in:user,health_expert,charity,community',
            ]);

            // If validation fails, return structured error response
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                '_tag' => $request->_tag,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'email_tag' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'error' => $validator->errors()
                ], 422);
            }

            // Find user by email or _tag
            $user = User::where('email', $request->email_tag)
                ->orWhere('_tag', $request->email_tag)
                ->first();

            // If user not found or password incorrect, return proper error
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'error' => ['Incorrect email, tag, or password']
                ], 401);
            }

            // Generate authentication token
            $token = $user->createToken('vital_aid')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    '_tag' => $user->_tag,
                    'role' => $user->role,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log out',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {

        Log::info('User endpoint hit');

        Log::info('User request received', [
            'headers' => $request->headers->all(),
            'token' => $request->bearerToken(),
            'query_params' => $request->query(),
            'body' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'user' => $request->user(),
            'message' => 'User fetched successfully'
        ]);
    }

}
