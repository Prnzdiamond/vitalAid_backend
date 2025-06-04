<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->pendingVerification();
            } elseif ($request->status === 'approved') {
                $query->verified();
            } elseif ($request->status === 'rejected') {
                $query->where('verification_status', 'rejected');
            }
        } else {
            $query->needsVerification();
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $verifications = $query->orderBy('verification_submitted_at', 'desc')->paginate(20);

        return view('admin.verifications.index', compact('verifications'));
    }

    public function show(User $user)
    {
        if (!in_array($user->role, ['health_expert', 'charity', 'community'])) {
            return redirect()->route('admin.verifications.index')
                ->with('error', 'User does not require verification.');
        }

        $requiredDocuments = $user->getRequiredDocumentsAttribute();
        $optionalDocuments = $user->getOptionalDocuments();
        $uploadedDocuments = $user->verification_documents ?? [];
        $missingDocuments = $user->getMissingDocuments();

        return view('admin.verifications.show', compact(
            'user',
            'requiredDocuments',
            'optionalDocuments',
            'uploadedDocuments',
            'missingDocuments'
        ));
    }

    public function approve(Request $request, User $user)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        $user->approveVerification(Auth::id());

        // Log the approval
        $this->logVerificationAction($user, 'approved', $request->approval_notes);

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Verification approved successfully.');
    }

    public function reject(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $user->rejectVerification($request->rejection_reason, Auth::id());

        // Log the rejection
        $this->logVerificationAction($user, 'rejected', $request->rejection_reason);

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Verification rejected successfully.');
    }

    public function reset(User $user)
    {
        $user->resetVerification();

        // Log the reset
        $this->logVerificationAction($user, 'reset', 'Verification reset by admin');

        return redirect()->back()
            ->with('success', 'Verification status reset successfully.');
    }

    public function downloadDocument(User $user, $documentType)
    {
        if (!$user->hasDocument($documentType)) {
            return redirect()->back()
                ->with('error', 'Document not found.');
        }

        $documentInfo = $user->getDocumentInfo($documentType);
        $filePath = $user->verification_documents[$documentType];

        if (!Storage::disk('public')->exists($filePath)) {
            return redirect()->back()
                ->with('error', 'Document file not found.');
        }

        return Storage::disk('public')->download($filePath, $documentInfo['filename']);
    }

    public function viewDocument(User $user, $documentType)
    {
        if (!$user->hasDocument($documentType)) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $documentInfo = $user->getDocumentInfo($documentType);

        return response()->json([
            'success' => true,
            'document' => $documentInfo
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,_id',
            'bulk_notes' => 'nullable|string|max:1000'
        ]);

        $users = User::whereIn('_id', $request->user_ids)
            ->where('verification_status', 'pending')
            ->get();

        foreach ($users as $user) {
            $user->approveVerification(Auth::id());
            $this->logVerificationAction($user, 'bulk_approved', $request->bulk_notes);
        }

        return redirect()->back()
            ->with('success', count($users) . ' verifications approved successfully.');
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,_id',
            'bulk_rejection_reason' => 'required|string|max:1000'
        ]);

        $users = User::whereIn('_id', $request->user_ids)
            ->where('verification_status', 'pending')
            ->get();

        foreach ($users as $user) {
            $user->rejectVerification($request->bulk_rejection_reason, Auth::id());
            $this->logVerificationAction($user, 'bulk_rejected', $request->bulk_rejection_reason);
        }

        return redirect()->back()
            ->with('success', count($users) . ' verifications rejected successfully.');
    }

    public function statistics()
    {
        $stats = [
            'pending' => User::pendingVerification()->count(),
            'approved' => User::verified()->count(),
            'rejected' => User::where('verification_status', 'rejected')->count(),
            'health_experts' => [
                'total' => User::where('role', 'health_expert')->count(),
                'verified' => User::where('role', 'health_expert')->verified()->count(),
                'pending' => User::where('role', 'health_expert')->pendingVerification()->count(),
            ],
            'charities' => [
                'total' => User::where('role', 'charity')->count(),
                'verified' => User::where('role', 'charity')->verified()->count(),
                'pending' => User::where('role', 'charity')->pendingVerification()->count(),
            ],
            'communities' => [
                'total' => User::where('role', 'community')->count(),
                'verified' => User::where('role', 'community')->verified()->count(),
                'pending' => User::where('role', 'community')->pendingVerification()->count(),
            ]
        ];

        return view('admin.verifications.statistics', compact('stats'));
    }

    private function logVerificationAction(User $user, $action, $notes = null)
    {
        // Implement logging logic here
        // This could be stored in a separate logs table or file
        Log::info("Verification {$action} for user {$user->id}", [
            'user_id' => $user->id,
            'action' => $action,
            'notes' => $notes,
            'admin_id' => Auth::id(),
            'timestamp' => now()
        ]);
    }
}