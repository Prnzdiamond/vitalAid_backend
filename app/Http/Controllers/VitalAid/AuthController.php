<?php

namespace App\Http\Controllers\VitalAid;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\VitalAid\CommunityMember;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Common base fields for all user roles
     */
    private const BASE_FIELDS = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        '_tag',
        'role'
    ];

    /**
     * Additional fields that may be present based on user role
     */
    private const ADDITIONAL_FIELDS = [
        'description',
        'location',
        'type',
        'visibility',
        'logo',
        'banner',
        'website',
        'social_links',
        'specialization',
        'qualifications',
        'available_hours',
        'experience_years',
        'registration_number',
        'founding_date',
        'mission_statement',
        'target_audience'
    ];

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            // Validate the request data
            $validator = $this->getRegistrationValidator($request);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Extract and prepare user data
            $userData = $this->prepareUserData($request);

            // Create user record
            $user = User::create($userData);

            // Add user as community admin if applicable
            if ($request->role === 'community') {
                $this->registerCommunityAdmin($user);
            }

            return $this->successResponse('User registered successfully.', ['user' => $user], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Registration failed.', $e);
        }
    }

    /**
     * Login a user
     */
    public function login(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'email_tag' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Find user and verify credentials
            $user = User::where('email', $request->email_tag)
                ->orWhere('_tag', $request->email_tag)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                    'errors' => ['Incorrect email, tag, or password']
                ], 401);
            }

            // Generate token
            $token = $user->createToken('vital_aid')->plainTextToken;

            // Prepare user data for response
            $userData = $this->getUserData($user);

            return $this->successResponse('Login successful.', [
                'token' => $token,
                'user' => $userData
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Login failed.', $e);
        }
    }

    /**
     * Logout a user
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return $this->successResponse('Logged out successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Logout failed.', $e);
        }
    }

    /**
     * Get authenticated user data
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();
            $userData = $this->getUserData($user);

            // For community role, include member count
            if ($user->role === 'community') {
                $userData['members_count'] = CommunityMember::where('community_id', $user->id)
                    ->where('status', 'active')
                    ->count();
            }

            return $this->successResponse('User fetched successfully.', ['user' => $userData]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch user data.', $e);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Get validation rules based on user role
            $validationRules = $this->getProfileUpdateRules($user->role);

            // Validate input
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Update user data
            $updateData = $request->only(array_keys($validationRules));
            $user->update($updateData);

            return $this->successResponse('Profile updated successfully.', ['user' => $user]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update profile.', $e);
        }
    }

    /**
     * Get validation rules for user registration
     */
    private function getRegistrationValidator(Request $request)
    {
        // Base validation rules for all users
        $baseRules = [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'phone_number' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
            '_tag' => 'required|regex:/^[a-zA-Z0-9*]{3,30}$/|unique:users,_tag',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,health_expert,charity,community',
        ];

        // Get role-specific rules
        $roleSpecificRules = $this->getRoleSpecificRules($request->input('role'));

        // Merge and validate
        return Validator::make($request->all(), array_merge($baseRules, $roleSpecificRules));
    }

    /**
     * Get role-specific validation rules
     */
    private function getRoleSpecificRules(string $role)
    {
        switch ($role) {
            case 'community':
                return [
                    'description' => 'nullable|string|max:1000',
                    'location' => 'nullable|string|max:255',
                    'type' => 'nullable|string|in:local,regional,national,international,special_interest',
                    'visibility' => 'nullable|string|in:public,private,invite_only',
                    'logo' => 'nullable|string',
                    'banner' => 'nullable|string',
                    'website' => 'nullable|url',
                    'social_links' => 'nullable|array',
                    'social_links.*.platform' => 'required|string',
                    'social_links.*.url' => 'required|url',
                ];
            case 'charity':
                return [
                    'description' => 'nullable|string|max:1000',
                    'location' => 'nullable|string|max:255',
                    'type' => 'nullable|string|in:nonprofit,foundation,ngo,cbo,other',
                    'visibility' => 'nullable|string|in:public,private',
                    'logo' => 'nullable|string',
                    'banner' => 'nullable|string',
                    'website' => 'nullable|url',
                    'social_links' => 'nullable|array',
                    'social_links.*.platform' => 'required|string',
                    'social_links.*.url' => 'required|url',
                    'registration_number' => 'nullable|string',
                    'founding_date' => 'nullable|date',
                    'mission_statement' => 'nullable|string|max:1000',
                    'target_audience' => 'nullable|string|max:500',
                ];
            case 'health_expert':
                return [
                    'description' => 'nullable|string|max:1000',
                    'location' => 'nullable|string|max:255',
                    'specialization' => 'nullable|string|max:255',
                    'qualifications' => 'nullable|string|max:500',
                    'available_hours' => 'nullable|array',
                    'experience_years' => 'nullable|integer|min:0',
                    'logo' => 'nullable|string',
                    'banner' => 'nullable|string',
                    'website' => 'nullable|url',
                    'social_links' => 'nullable|array',
                    'social_links.*.platform' => 'required|string',
                    'social_links.*.url' => 'required|url',
                ];
            default:
                return [];
        }
    }

    /**
     * Get validation rules for profile updates
     */
    private function getProfileUpdateRules(string $role)
    {
        // Base rules
        $baseRules = [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone_number' => 'nullable|regex:/^\+?[1-9]\d{1,14}$/',
        ];

        // Add role-specific rules
        return array_merge($baseRules, $this->getRoleSpecificRules($role));
    }

    /**
     * Prepare user data for database insertion
     */
    private function prepareUserData(Request $request)
    {
        // Extract base fields
        $userData = $request->only(self::BASE_FIELDS);

        // Hash the password
        $userData['password'] = Hash::make($request->password);

        // Add role-specific fields
        foreach (self::ADDITIONAL_FIELDS as $field) {
            if ($request->has($field)) {
                $userData[$field] = $request->input($field);
            }
        }

        return $userData;
    }

    /**
     * Register the user as a community admin
     */
    private function registerCommunityAdmin(User $user)
    {
        CommunityMember::create([
            'community_id' => $user->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'joined_at' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Get formatted user data for API responses
     */
    private function getUserData(User $user)
    {
        // Start with base fields
        $userData = $user->only(array_merge(['id'], self::BASE_FIELDS));

        // Add additional fields if they exist
        foreach (self::ADDITIONAL_FIELDS as $field) {
            if (isset($user->$field)) {
                $userData[$field] = $user->$field;
            }
        }

        return $userData;
    }

    /**
     * Create a success response
     */
    private function successResponse(string $message, array $data = [], int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Create an error response for validation failures
     */
    private function validationErrorResponse($errors)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error.',
            'errors' => $errors
        ], 422);
    }

    /**
     * Create an error response for exceptions
     */
    private function errorResponse(string $message, Exception $exception)
    {
        Log::error("$message: " . $exception->getMessage());

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $exception->getMessage()
        ], 500);
    }
}
