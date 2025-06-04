<!-- Bulk Approve Modal -->
<div id="bulk-approve-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900">Bulk Approve Verifications</h3>
                <button onclick="hideModal('bulk-approve-modal')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to approve <span id="bulk-approve-count"
                                class="font-semibold text-gray-900">0</span> verification request(s)?
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            This action will grant verification status to all selected users and cannot be undone.
                        </p>
                    </div>
                </div>

                <!-- Optional Notes -->
                <div class="mb-4">
                    <label for="bulk-approve-notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Approval Notes (Optional)
                    </label>
                    <textarea id="bulk-approve-notes" name="notes" rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                        placeholder="Add any notes about this bulk approval...">
                    </textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <button onclick="hideModal('bulk-approve-modal')"
                    class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkApprove()"
                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <span class="flex items-center">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Approve Selected
                    </span>
                </button>
            </div>
        </div>

        <!-- Hidden Form -->
        <form id="bulk-approve-form" action="{{ route('admin.verifications.bulk-approve') }}" method="POST"
            style="display: none;">
            @csrf
            <input type="hidden" name="notes" id="bulk-approve-notes-input">
        </form>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div id="bulk-reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900">Bulk Reject Verifications</h3>
                <button onclick="hideModal('bulk-reject-modal')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to reject <span id="bulk-reject-count"
                                class="font-semibold text-gray-900">0</span> verification request(s)?
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            This action will deny verification status for all selected users.
                        </p>
                    </div>
                </div>

                <!-- Required Rejection Reason -->
                <div class="mb-4">
                    <label for="bulk-reject-reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="bulk-reject-reason" name="reason" rows="3" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                        placeholder="Please provide a reason for rejecting these verification requests...">
                    </textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        This reason will be sent to all affected users via email notification.
                    </p>
                </div>

                <!-- Additional Notes -->
                <div class="mb-4">
                    <label for="bulk-reject-notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Additional Notes (Optional)
                    </label>
                    <textarea id="bulk-reject-notes" name="notes" rows="2"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                        placeholder="Add any internal notes about this bulk rejection...">
                    </textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <button onclick="hideModal('bulk-reject-modal')"
                    class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkReject()"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                    <span class="flex items-center">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject Selected
                    </span>
                </button>
            </div>
        </div>

        <!-- Hidden Form -->
        <form id="bulk-reject-form" action="{{ route('admin.verifications.bulk-reject') }}" method="POST"
            style="display: none;">
            @csrf
            <input type="hidden" name="reason" id="bulk-reject-reason-input">
            <input type="hidden" name="notes" id="bulk-reject-notes-input">
        </form>
    </div>
</div>

<style>
    /* Custom styles for the modals */
    .modal-overlay {
        backdrop-filter: blur(4px);
    }

    /* Animation for modal appearance */
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #bulk-approve-modal:not(.hidden)>div,
    #bulk-reject-modal:not(.hidden)>div {
        animation: modalSlideIn 0.3s ease-out;
    }

    /* Focus styles for better accessibility */
    textarea:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Error state for required fields */
    .error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    /* Success animation for buttons */
    button:active {
        transform: translateY(1px);
    }
</style>

<script>
    // Enhanced JavaScript functions for bulk actions
function submitBulkApprove() {
    const form = document.getElementById('bulk-approve-form');
    const notesTextarea = document.getElementById('bulk-approve-notes');
    const notesInput = document.getElementById('bulk-approve-notes-input');

    // Transfer notes to hidden input
    notesInput.value = notesTextarea.value;

    // Add user IDs to form
    const userIdsInput = document.createElement('input');
    userIdsInput.type = 'hidden';
    userIdsInput.name = 'user_ids';
    userIdsInput.value = JSON.stringify(selectedUserIds);
    form.appendChild(userIdsInput);

    // Show loading state
    const submitBtn = form.parentNode.querySelector('button[onclick="submitBulkApprove()"]');
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

    form.submit();
}

function submitBulkReject() {
    const form = document.getElementById('bulk-reject-form');
    const reasonTextarea = document.getElementById('bulk-reject-reason');
    const notesTextarea = document.getElementById('bulk-reject-notes');
    const reasonInput = document.getElementById('bulk-reject-reason-input');
    const notesInput = document.getElementById('bulk-reject-notes-input');

    // Validate required reason field
    if (!reasonTextarea.value.trim()) {
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
    reasonTextarea.classList.remove('error');
    const existingError = reasonTextarea.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Transfer values to hidden inputs
    reasonInput.value = reasonTextarea.value;
    notesInput.value = notesTextarea.value;

    // Add user IDs to form
    const userIdsInput = document.createElement('input');
    userIdsInput.type = 'hidden';
    userIdsInput.name = 'user_ids';
    userIdsInput.value = JSON.stringify(selectedUserIds);
    form.appendChild(userIdsInput);

    // Show loading state
    const submitBtn = form.parentNode.querySelector('button[onclick="submitBulkReject()"]');
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

    form.submit();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const bulkApproveModal = document.getElementById('bulk-approve-modal');
    const bulkRejectModal = document.getElementById('bulk-reject-modal');

    if (event.target === bulkApproveModal) {
        hideModal('bulk-approve-modal');
    }
    if (event.target === bulkRejectModal) {
        hideModal('bulk-reject-modal');
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideModal('bulk-approve-modal');
        hideModal('bulk-reject-modal');
    }
});

// Clear form data when modal is hidden
function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');

    // Reset form data
    if (modalId === 'bulk-approve-modal') {
        document.getElementById('bulk-approve-notes').value = '';
    } else if (modalId === 'bulk-reject-modal') {
        document.getElementById('bulk-reject-reason').value = '';
        document.getElementById('bulk-reject-notes').value = '';

        // Remove any error styling
        const reasonTextarea = document.getElementById('bulk-reject-reason');
        reasonTextarea.classList.remove('error');
        const existingError = reasonTextarea.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
    }
}
</script>