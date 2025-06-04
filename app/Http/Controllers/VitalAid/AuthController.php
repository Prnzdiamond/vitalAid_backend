<?php

namespace App\Http\Controllers\VitalAid;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\VitalAid\CommunityMember;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VitalAid\UserResource;

class AuthController extends Controller
{
    private const BASE_FIELDS = ['first_name', 'last_name', 'email', 'phone_number', '_tag', 'role'];
    private const ADDITIONAL_FIELDS = ['description', 'location', 'type', 'visibility', 'logo', 'banner', 'website', 'social_links', 'specialization', 'qualifications', 'available_hours', 'experience_years', 'registration_number', 'founding_date', 'mission_statement', 'target_audience'];

    public function register(Request $request)
    {
        try {
            $validator = $this->getRegistrationValidator($request);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $userData = $this->prepareUserData($request);
            $user = User::create($userData);

            if ($request->role === 'community')
                $this->registerCommunityAdmin($user);

            return $this->successResponse('User registered successfully.', [
                'user' => (new UserResource($user))->toArray($request),
                'token' => $user->createToken('vital_aid')->plainTextToken
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Registration failed.', $e);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_tag' => 'required|string',
                'password' => 'required|string',
            ]);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $user = User::where('email', $request->email_tag)->orWhere('_tag', $request->email_tag)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                    'errors' => ['Incorrect email, tag, or password']
                ], 401);
            }

            return $this->successResponse('Login successful.', [
                'token' => $user->createToken('vital_aid')->plainTextToken,
                'user' => (new UserResource($user))->toArray($request)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Login failed.', $e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return $this->successResponse('Logged out successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Logout failed.', $e);
        }
    }

    public function user(Request $request)
    {
        try {
            return $this->successResponse('User fetched successfully.', [
                'user' => (new UserResource($request->user()))->toArray($request)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch user data.', $e);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $validationRules = $this->getProfileUpdateRules($user->role);
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $updateData = $request->only(array_keys($validationRules));
            if ($request->has('password') && !empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }
            $user->update($updateData);

            return $this->successResponse('Profile updated successfully.', [
                'user' => (new UserResource($user->fresh()))->toArray($request)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update profile.', $e);
        }
    }

    public function submitVerification(Request $request)
    {
        try {
            $user = $request->user();
            if (!in_array($user->role, ['health_expert', 'charity', 'community'])) {
                return response()->json(['success' => false, 'message' => 'Your account type does not require verification.'], 400);
            }

            $validator = Validator::make($request->all(), [
                'documents' => 'required|array|min:1',
                'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120'
            ]);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $requiredDocs = $user->getRequiredDocumentsAttribute();
            $providedDocs = $request->file('documents', []);

            $invalidDocs = array_filter(array_keys($providedDocs), fn($docType) => !array_key_exists($docType, $requiredDocs));
            if (!empty($invalidDocs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document types provided.',
                    'invalid_documents' => $invalidDocs,
                    'valid_document_types' => array_keys($requiredDocs)
                ], 422);
            }

            $uploadedDocuments = [];
            foreach ($providedDocs as $docType => $file) {
                try {
                    $uploadedDocuments[$docType] = $this->storeVerificationDocument($user, $docType, $file);
                } catch (Exception $e) {
                    $this->cleanupUploadedFiles($uploadedDocuments);
                    throw new Exception("Failed to upload {$docType}: " . $e->getMessage());
                }
            }

            $this->deleteOldDocuments($user, $uploadedDocuments);
            $user->verification_documents = array_merge($user->verification_documents ?? [], $uploadedDocuments);

            $oldStatus = $user->verification_status; // Store the old status first

            if (empty($user->verification_status) || $user->verification_status === 'rejected') {
                $user->verification_status = 'pending';
                $user->verification_submitted_at = now();
                if ($oldStatus === 'rejected') {
                    $user->verification_rejection_reason = null;
                    $user->verification_rejected_at = null;
                }
            }
            $user->save();
            $user->refresh();

            $isComplete = $user->hasCompleteDocuments();
            return $this->successResponse(
                $isComplete ? 'All required documents submitted successfully. Your verification is under review.' : 'Documents submitted successfully. You can continue adding remaining documents.',
                [
                    'user' => (new UserResource($user))->toArray($request),
                    'verification_progress' => $user->verification_progress,
                    'missing_documents' => $user->getMissingDocuments(),
                    'is_complete' => $isComplete,
                    'documents_submitted' => array_keys($uploadedDocuments)
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to submit verification documents.', $e);
        }
    }

    public function updateVerificationDocument(Request $request)
    {
        try {
            $user = $request->user();
            if (!in_array($user->role, ['health_expert', 'charity', 'community'])) {
                return response()->json(['success' => false, 'message' => 'Your account type does not require verification.'], 400);
            }

            $validator = Validator::make($request->all(), [
                'document_type' => 'required|string',
                'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120'
            ]);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $documentType = $request->input('document_type');
            $allValidDocs = array_merge($user->getRequiredDocumentsAttribute(), $user->getOptionalDocuments());

            if (!array_key_exists($documentType, $allValidDocs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type for your role.',
                    'valid_document_types' => array_keys($allValidDocs)
                ], 422);
            }

            $filePath = $this->storeVerificationDocument($user, $documentType, $request->file('document'));
            $currentDocuments = $user->verification_documents ?? [];

            if (isset($currentDocuments[$documentType])) {
                $this->deleteDocumentFile($currentDocuments[$documentType]);
            }

            $currentDocuments[$documentType] = $filePath;
            $user->verification_documents = $currentDocuments;

            if (empty($user->verification_status) || $user->verification_status === 'rejected') {
                $user->verification_status = 'pending';
                $user->verification_submitted_at = now();
                $user->verification_rejection_reason = null;
                $user->verification_rejected_at = null;
            }
            $user->save();
            $user->refresh();

            return $this->successResponse('Document updated successfully.', [
                'user' => (new UserResource($user))->toArray($request),
                'verification_progress' => $user->verification_progress,
                'missing_documents' => $user->getMissingDocuments(),
                'is_complete' => $user->hasCompleteDocuments(),
                'updated_document' => $documentType
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update verification document.', $e);
        }
    }

    public function removeVerificationDocument(Request $request)
    {
        try {
            $user = $request->user();
            if (!in_array($user->role, ['health_expert', 'charity', 'community'])) {
                return response()->json(['success' => false, 'message' => 'Your account type does not require verification.'], 400);
            }

            $validator = Validator::make($request->all(), ['document_type' => 'required|string']);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $documentType = $request->input('document_type');
            $currentDocuments = $user->verification_documents ?? [];

            if (!array_key_exists($documentType, $currentDocuments)) {
                return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
            }

            $this->deleteDocumentFile($currentDocuments[$documentType]);
            unset($currentDocuments[$documentType]);
            $user->verification_documents = $currentDocuments;

            if (empty($currentDocuments)) {
                $user->verification_status = null;
                $user->verification_submitted_at = null;
            }
            $user->save();
            $user->refresh();

            return $this->successResponse('Document removed successfully.', [
                'user' => (new UserResource($user))->toArray($request),
                'verification_progress' => $user->verification_progress,
                'missing_documents' => $user->getMissingDocuments(),
                'is_complete' => $user->hasCompleteDocuments(),
                'removed_document' => $documentType
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to remove verification document.', $e);
        }
    }

    public function downloadVerificationDocument(Request $request, $documentId)
    {
        try {
            $user = $request->user();
            if ($user->id !== $documentId && !$user->isRole('admin')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to document.'], 403);
            }

            $targetUser = $user->id === $documentId ? $user : User::findOrFail($documentId);
            $validator = Validator::make($request->all(), ['document_type' => 'required|string']);
            if ($validator->fails())
                return $this->validationErrorResponse($validator->errors());

            $documentType = $request->input('document_type');
            $userDocuments = $targetUser->verification_documents ?? [];

            if (!isset($userDocuments[$documentType])) {
                return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
            }

            $filePath = $userDocuments[$documentType];
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'File not found on server.'], 404);
            }

            return Storage::disk('public')->download($filePath);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to download document.', $e);
        }
    }

    public function verificationStatus(Request $request)
    {
        try {
            $user = $request->user();
            $data = [
                'is_verified' => $user->isVerified(),
                'verification_status' => $user->verification_status,
                'verification_progress' => $user->verification_progress,
                'required_documents' => $user->required_documents,
                'missing_documents' => $user->getMissingDocuments(),
                'has_complete_documents' => $user->hasCompleteDocuments(),
                'optional_documents' => $user->getOptionalDocuments(),
            ];

            if ($user->verification_status === 'rejected') {
                $data['rejection_reason'] = $user->verification_rejection_reason;
                $data['rejected_at'] = $user->verification_rejected_at;
            }

            if ($user->verification_status === 'approved') {
                $data['approved_at'] = $user->verification_approved_at;
                $data['verified_by'] = $user->verified_by;
            }

            return $this->successResponse('Verification status fetched successfully.', $data);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch verification status.', $e);
        }
    }

    private function getRegistrationValidator(Request $request)
    {
        $baseRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'phone_number' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
            '_tag' => 'required|regex:/^[a-zA-Z0-9*]{3,30}$/|unique:users,_tag',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,health_expert,charity,community',
        ];

        return Validator::make($request->all(), array_merge($baseRules, $this->getRoleSpecificRules($request->input('role'))));
    }

    private function getRoleSpecificRules(string $role)
    {
        $commonOrgRules = [
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:500',
            'banner' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string|max:50',
            'social_links.*.url' => 'required|url|max:255',
        ];

        return match ($role) {
            'community' => array_merge($commonOrgRules, [
                'type' => 'nullable|string|in:local,regional,national,international,special_interest',
                'visibility' => 'nullable|string|in:public,private,invite_only',
            ]),
            'charity' => array_merge($commonOrgRules, [
                'type' => 'nullable|string|in:nonprofit,foundation,ngo,cbo,other',
                'visibility' => 'nullable|string|in:public,private',
                'registration_number' => 'nullable|string|max:100',
                'founding_date' => 'nullable|date|before:today',
                'mission_statement' => 'nullable|string|max:1000',
                'target_audience' => 'nullable|string|max:500',
            ]),
            'health_expert' => array_merge($commonOrgRules, [
                'specialization' => 'nullable|string|max:255',
                'qualifications' => 'nullable|string|max:500',
                'available_hours' => 'nullable|array',
                'available_hours.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'available_hours.*.start_time' => 'required|date_format:H:i',
                'available_hours.*.end_time' => 'required|date_format:H:i|after:available_hours.*.start_time',
                'experience_years' => 'nullable|integer|min:0|max:60',
            ]),
            default => []
        };
    }

    private function getProfileUpdateRules(string $role)
    {
        $baseRules = [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|regex:/^\+?[1-9]\d{1,14}$/',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $roleRules = $this->getRoleSpecificRules($role);
        foreach ($roleRules as $field => $rule) {
            $roleRules[$field] = str_replace('required', 'nullable', $rule);
        }

        return array_merge($baseRules, $roleRules);
    }

    private function prepareUserData(Request $request)
    {
        $userData = $request->only(self::BASE_FIELDS);
        $userData['password'] = Hash::make($request->password);

        foreach (self::ADDITIONAL_FIELDS as $field) {
            if ($request->has($field))
                $userData[$field] = $request->input($field);
        }

        if (in_array($request->role, ['community', 'charity']) && !isset($userData['visibility'])) {
            $userData['visibility'] = 'public';
        }

        return $userData;
    }

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

    private function storeVerificationDocument(User $user, string $documentType, $file)
    {
        $directory = "verification/{$user->id}/{$user->role}";
        $extension = $file->getClientOriginalExtension();
        $fileName = $documentType . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $file->storeAs($directory, $fileName, 'public');

        if (!$filePath)
            throw new Exception('Failed to store file');
        return $filePath;
    }

    private function deleteOldDocuments(User $user, array $newDocuments)
    {
        $currentDocuments = $user->verification_documents ?? [];
        foreach ($newDocuments as $docType => $newPath) {
            if (isset($currentDocuments[$docType])) {
                $this->deleteDocumentFile($currentDocuments[$docType]);
            }
        }
    }

    private function deleteDocumentFile(string $filePath)
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        } catch (Exception $e) {
            Log::warning("Failed to delete verification document: {$filePath}. Error: " . $e->getMessage());
        }
    }

    private function cleanupUploadedFiles(array $filePaths)
    {
        foreach ($filePaths as $filePath)
            $this->deleteDocumentFile($filePath);
    }

    private function successResponse(string $message, array $data = [], int $status = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    private function validationErrorResponse($errors)
    {
        return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $errors], 422);
    }

    private function errorResponse(string $message, Exception $exception)
    {
        Log::error("$message: " . $exception->getMessage());
        return response()->json(['success' => false, 'message' => $message, 'error' => $exception->getMessage()], 500);
    }
}