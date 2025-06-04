@extends('admin.layouts.layout')

@section('title', 'Verification Details - ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.verifications.index') }}"
                    class="text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Verification Details</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $user->name }} - {{ ucfirst(str_replace('_', ' ',
                        $user->role)) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($user->verification_status === 'pending')
                <button onclick="showApproveModal('{{ $user->_id }}', '{{ $user->name }}')"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Approve
                </button>
                <button onclick="showRejectModal('{{ $user->_id }}', '{{ $user->name }}')"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Reject
                </button>
                @endif

                @if(in_array($user->verification_status, ['approved', 'rejected']))
                <button onclick="showResetModal('{{ $user->_id }}', '{{ $user->name }}')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Reset Verification
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>

                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div
                                class="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium text-lg">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->role === 'health_expert' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->role === 'charity' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $user->role === 'community' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </dd>
                        </div>

                        @if($user->phone_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone_number }}</dd>
                        </div>
                        @endif

                        @if($user->location)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->location }}</dd>
                        </div>
                        @endif

                        @if($user->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->description }}</dd>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Verification Status</h3>

                <div class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current Status</dt>
                        <dd class="mt-1">
                            @if($user->verification_status === 'pending')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending Review
                            </span>
                            @elseif($user->verification_status === 'approved')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Approved
                            </span>
                            @elseif($user->verification_status === 'rejected')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Rejected
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Not Submitted
                            </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Progress</dt>
                        <dd class="mt-1">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-indigo-600 h-2 rounded-full"
                                        style="width: {{ $user->verification_progress }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 font-medium">{{ $user->verification_progress
                                    }}%</span>
                            </div>
                        </dd>
                    </div>

                    @if($user->verification_submitted_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Submitted</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->verification_submitted_at->format('M j, Y g:i
                            A') }}</dd>
                    </div>
                    @endif

                    @if($user->verification_approved_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Approved</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->verification_approved_at->format('M j, Y g:i
                            A') }}</dd>
                    </div>
                    @endif

                    @if($user->verification_rejected_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Rejected</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->verification_rejected_at->format('M j, Y g:i
                            A') }}</dd>
                    </div>
                    @endif

                    @if($user->verification_rejection_reason)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                        <dd class="mt-1 text-sm text-red-600 bg-red-50 p-3 rounded-md">{{
                            $user->verification_rejection_reason }}</dd>
                    </div>
                    @endif

                    @if($user->verified_by)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Verified By</dt>
                        <dd class="mt-1 text-sm text-gray-900">Admin ID: {{ $user->verified_by }}</dd>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Verification Documents</h3>
                    <span class="text-sm text-gray-500">
                        {{ count($user->verification_documents ?? []) }} of {{ count($user->required_documents) }}
                        required documents uploaded
                    </span>
                </div>

                <!-- Required Documents -->
                <div class="space-y-6">
                    <div>
                        <h4 class="text-md font-medium text-gray-800 mb-4">Required Documents</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($user->required_documents as $docType => $docName)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="text-sm font-medium text-gray-900">{{ $docName }}</h5>
                                        <p class="text-xs text-gray-500 mt-1">{{ ucfirst(str_replace('_', ' ',
                                            $docType)) }}</p>
                                    </div>

                                    @if($user->hasDocument($docType))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Uploaded
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Missing
                                    </span>
                                    @endif
                                </div>

                                @if($user->hasDocument($docType))
                                @php $docInfo = $user->getDocumentInfo($docType); @endphp
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center text-xs text-gray-500">
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        {{ $docInfo['filename'] }}
                                    </div>

                                    @if(isset($docInfo['size']))
                                    <div class="flex items-center text-xs text-gray-500">
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2m5 0v18a2 2 0 01-2 2H6a2 2 0 01-2-2V4h14z">
                                            </path>
                                        </svg>
                                        {{ number_format($docInfo['size'] / 1024, 1) }} KB
                                    </div>
                                    @endif

                                    <div class="flex items-center space-x-2 pt-2">
                                        <a href="{{ $docInfo['url'] }}" target="_blank"
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ $docInfo['url'] }}" download="{{ $docInfo['filename'] }}"
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Optional Documents -->
                    @if(!empty($user->getOptionalDocuments()))
                    <div>
                        <h4 class="text-md font-medium text-gray-800 mb-4">Optional Documents</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($user->getOptionalDocuments() as $docType => $docName)
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="text-sm font-medium text-gray-700">{{ $docName }}</h5>
                                        <p class="text-xs text-gray-500 mt-1">Optional</p>
                                    </div>

                                    @if($user->hasDocument($docType))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Uploaded
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Not Provided
                                    </span>
                                    @endif
                                </div>

                                @if($user->hasDocument($docType))
                                @php $docInfo = $user->getDocumentInfo($docType); @endphp
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center text-xs text-gray-500">
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        {{ $docInfo['filename'] }}
                                    </div>

                                    <div class="flex items-center space-x-2 pt-2">
                                        <a href="{{ $docInfo['url'] }}" target="_blank"
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ $docInfo['url'] }}" download="{{ $docInfo['filename'] }}"
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.verifications.modals.approve')
@include('admin.verifications.modals.reject')
@include('admin.verifications.modals.reset')

@endsection
@push('scripts')
<script>
    let selectedUserIds = [];
    const rejectRouteBase = @json(route('admin.verifications.reject', 'REPLACE_ID'));
    const approveRouteBase = @json(route('admin.verifications.approve', 'REPLACE_ID'));
    const resetRouteBase = @json(route('admin.verifications.reset', 'REPLACE_ID'));






    // Individual Modal Functions
    function showApproveModal(userId, userName) {
        const approveUrl = approveRouteBase.replace('REPLACE_ID', userId);
        document.getElementById('approve-user-id').value = userId;
        document.getElementById('approve-user-name').textContent = userName;
        document.getElementById('approve-form').action = approveUrl;
        document.getElementById('approve-modal').classList.remove('hidden');
        // Clear previous notes
        document.getElementById('approval_notes').value = '';
    }

    function showRejectModal(userId, userName) {
        const rejectUrl = rejectRouteBase.replace('REPLACE_ID', userId);
        document.getElementById('reject-user-id').value = userId;
        document.getElementById('reject-form').action = rejectUrl;
        document.getElementById('reject-user-name').textContent = userName;
        document.getElementById('rejection_reason').value = '';
        document.getElementById('reject-modal').classList.remove('hidden');
    }

    function showResetModal(userId, userName) {
        const resetUrl = resetRouteBase.replace('REPLACE_ID', userId);
        document.getElementById('reset-user-id').value = userId;
        document.getElementById('reset-user-name').textContent = userName;
        document.getElementById('reset-form').action = resetUrl;
        document.getElementById('reset-modal').classList.remove('hidden');
        // Clear previous values
        if (document.getElementById('reset_reason')) {
            document.getElementById('reset_reason').value = '';
        }
        if (document.getElementById('reset-confirm')) {
            document.getElementById('reset-confirm').checked = false;
        }
        if (document.getElementById('reset-submit-btn')) {
            document.getElementById('reset-submit-btn').disabled = true;
        }
    }




    // Helper Functions
    function setRejectionReason(reason) {
        if (document.getElementById('rejection_reason')) {
            document.getElementById('rejection_reason').value = reason;
        }
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Reset confirmation checkbox handler
        const resetConfirm = document.getElementById('reset-confirm');
        const resetSubmitBtn = document.getElementById('reset-submit-btn');

        if (resetConfirm && resetSubmitBtn) {
            resetConfirm.addEventListener('change', function() {
                resetSubmitBtn.disabled = !this.checked;
            });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modals = ['bulk-approve-modal', 'bulk-reject-modal', 'approve-modal', 'reject-modal', 'reset-modal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && event.target === modal) {
                    hideModal(modalId);
                }
            });
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = ['bulk-approve-modal', 'bulk-reject-modal', 'approve-modal', 'reject-modal', 'reset-modal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        hideModal(modalId);
                    }
                });
            }
        });
    });
</script>
@endpush