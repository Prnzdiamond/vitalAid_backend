@extends('admin.layouts.layout')

@section('title', 'Verification Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Verification Management</h2>
                <p class="mt-1 text-sm text-gray-600">Review and manage user verification requests</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.verifications.statistics') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    View Statistics
                </a>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.verifications.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Approved
                        </option>
                        <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Rejected
                        </option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" id="role"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Roles</option>
                        <option value="health_expert" {{ request('role')==='health_expert' ? 'selected' : '' }}>Health
                            Expert</option>
                        <option value="charity" {{ request('role')==='charity' ? 'selected' : '' }}>Charity</option>
                        <option value="community" {{ request('role')==='community' ? 'selected' : '' }}>Community
                        </option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name or email..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('admin.verifications.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition-colors">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4" id="bulk-actions" style="display: none;">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">
                <span id="selected-count">0</span> item(s) selected
            </span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="showBulkApproveModal()"
                    class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    Bulk Approve
                </button>
                <button type="button" onclick="showBulkRejectModal()"
                    class="px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                    Bulk Reject
                </button>
                <button type="button" onclick="clearSelection()"
                    class="px-3 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 transition-colors">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Verifications Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($verifications as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            @if($user->verification_status === 'pending')
                            <input type="checkbox" name="user_ids[]" value="{{ $user->_id }}"
                                onchange="updateBulkActions()"
                                class="user-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div
                                        class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $user->role === 'health_expert' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'charity' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->role === 'community' ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->verification_status === 'pending')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending
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
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-indigo-600 h-2 rounded-full"
                                        style="width: {{ $user->verification_progress }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $user->verification_progress }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->verification_submitted_at ? $user->verification_submitted_at->format('M j, Y') :
                            'Not submitted' }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                            <a href="{{ route('admin.verifications.show', $user) }}"
                                class="text-indigo-600 hover:text-indigo-900">View</a>

                            @if($user->verification_status === 'pending')
                            <button onclick="showApproveModal('{{ $user->_id }}', '{{ $user->name }}')"
                                class="text-green-600 hover:text-green-900">Approve</button>
                            <button onclick="showRejectModal('{{ $user->_id }}', '{{ $user->name }}')"
                                class="text-red-600 hover:text-red-900">Reject</button>
                            @endif

                            @if(in_array($user->verification_status, ['approved', 'rejected']))
                            <button onclick="showResetModal('{{ $user->_id }}', '{{ $user->name }}')"
                                class="text-gray-600 hover:text-gray-900">Reset</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No verifications found</h3>
                                <p class="mt-1 text-sm text-gray-500">No verification requests match your current
                                    filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($verifications->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modals -->
@include('admin.verifications.modals.approve')
@include('admin.verifications.modals.reject')
@include('admin.verifications.modals.reset')
@include('admin.verifications.modals.bulk-actions')

@endsection

@push('scripts')
<script>
    let selectedUserIds = [];
    const rejectRouteBase = @json(route('admin.verifications.reject', 'REPLACE_ID'));
    const approveRouteBase = @json(route('admin.verifications.approve', 'REPLACE_ID'));
    const resetRouteBase = @json(route('admin.verifications.reset', 'REPLACE_ID'));

    function toggleSelectAll() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.user-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');

        selectedUserIds = Array.from(checkboxes).map(cb => cb.value);
        selectedCount.textContent = selectedUserIds.length;

        if (selectedUserIds.length > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
        }
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('select-all').checked = false;
        updateBulkActions();
    }

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

    // Bulk Modal Functions
    function showBulkApproveModal() {
        document.getElementById('bulk-approve-count').textContent = selectedUserIds.length;
        document.getElementById('bulk-approve-modal').classList.remove('hidden');
    }

    function showBulkRejectModal() {
        document.getElementById('bulk-reject-count').textContent = selectedUserIds.length;
        document.getElementById('bulk-reject-modal').classList.remove('hidden');
    }

    // Hide Modal Function
    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');

        // Reset form data based on modal type
        if (modalId === 'bulk-approve-modal') {
            if (document.getElementById('bulk-approve-notes')) {
                document.getElementById('bulk-approve-notes').value = '';
            }
        } else if (modalId === 'bulk-reject-modal') {
            if (document.getElementById('bulk-reject-reason')) {
                document.getElementById('bulk-reject-reason').value = '';
            }
            if (document.getElementById('bulk-reject-notes')) {
                document.getElementById('bulk-reject-notes').value = '';
            }
            // Remove any error styling
            const reasonTextarea = document.getElementById('bulk-reject-reason');
            if (reasonTextarea) {
                reasonTextarea.classList.remove('error');
                const existingError = reasonTextarea.parentNode.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
            }
        }
    }

    // Bulk Submit Functions
    function submitBulkApprove() {
        const form = document.getElementById('bulk-approve-form');
        const notesTextarea = document.getElementById('bulk-approve-notes');
        const notesInput = document.getElementById('bulk-approve-notes-input');

        // Transfer notes to hidden input if they exist
        if (notesTextarea && notesInput) {
            notesInput.value = notesTextarea.value;
        }

        // Add user IDs to form
        const userIdsInput = document.createElement('input');
        userIdsInput.type = 'hidden';
        userIdsInput.name = 'user_ids';
        userIdsInput.value = JSON.stringify(selectedUserIds);
        form.appendChild(userIdsInput);

        // Show loading state
        const submitBtn = form.parentNode.querySelector('button[onclick="submitBulkApprove()"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <span class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            `;
            submitBtn.disabled = true;
        }

        form.submit();
    }

    function submitBulkReject() {
        const form = document.getElementById('bulk-reject-form');
        const reasonTextarea = document.getElementById('bulk-reject-reason');
        const notesTextarea = document.getElementById('bulk-reject-notes');
        const reasonInput = document.getElementById('bulk-reject-reason-input');
        const notesInput = document.getElementById('bulk-reject-notes-input');

        // Validate required reason field
        if (reasonTextarea && !reasonTextarea.value.trim()) {
            reasonTextarea.classList.add('error');
            reasonTextarea.focus();

            // Show error message
            let errorMsg = reasonTextarea.parentNode.querySelector('.error-message');
            if (!errorMsg) {
                errorMsg = document.createElement('p');
                errorMsg.className = 'error-message text-xs text-red-600 mt-1';
                errorMsg.textContent = 'Please provide a reason for rejection.';
                reasonTextarea.parentNode.appendChild(errorMsg);
            }
            return;
        }

        // Remove error styling if present
        if (reasonTextarea) {
            reasonTextarea.classList.remove('error');
            const existingError = reasonTextarea.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
        }

        // Transfer values to hidden inputs
        if (reasonTextarea && reasonInput) {
            reasonInput.value = reasonTextarea.value;
        }
        if (notesTextarea && notesInput) {
            notesInput.value = notesTextarea.value;
        }

        // Add user IDs to form
        const userIdsInput = document.createElement('input');
        userIdsInput.type = 'hidden';
        userIdsInput.name = 'user_ids';
        userIdsInput.value = JSON.stringify(selectedUserIds);
        form.appendChild(userIdsInput);

        // Show loading state
        const submitBtn = form.parentNode.querySelector('button[onclick="submitBulkReject()"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <span class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            `;
            submitBtn.disabled = true;
        }

        form.submit();
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