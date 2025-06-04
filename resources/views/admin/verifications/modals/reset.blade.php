<!-- Reset Verification Modal -->
<div id="reset-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-semibold text-gray-900">Reset Verification</h3>
                <button onclick="hideModal('reset-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="mt-4">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Reset verification for:</h4>
                        <p class="text-sm text-gray-600" id="reset-user-name"></p>
                    </div>
                </div>

                <form id="reset-form" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="reset-user-id" name="user_id" value="">

                    <div class="mb-4">
                        <label for="reset_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reset Reason (Optional)
                        </label>
                        <textarea id="reset_reason" name="reset_reason" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm"
                            placeholder="Provide a reason for resetting the verification status (optional)..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">This will be logged for audit purposes</p>
                    </div>

                    <!-- Warning Message -->
                    <div class="bg-orange-50 border border-orange-200 rounded-md p-3 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-orange-800">
                                    <strong>Warning: This action will:</strong>
                                </p>
                                <ul class="text-sm text-orange-700 mt-1 space-y-1">
                                    <li>• Clear the current verification status</li>
                                    <li>• Reset verification progress to 0%</li>
                                    <li>• Remove approval/rejection timestamps</li>
                                    <li>• Allow user to resubmit verification</li>
                                    <li>• Keep existing documents (if any)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation Checkbox -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="reset-confirm" required
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-sm text-gray-700">
                                I understand this will reset the verification status and allow resubmission
                            </span>
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="hideModal('reset-modal')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="reset-submit-btn" disabled
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <svg class="inline-block mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Reset Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Update form action when modal is shown
function showResetModal(userId, userName) {
    document.getElementById('reset-user-id').value = userId;
    document.getElementById('reset-user-name').textContent = userName;
    document.getElementById('reset-form').action = `/admin/verifications/${userId}/reset`;
    document.getElementById('reset-modal').classList.remove('hidden');
    // Clear previous values
    document.getElementById('reset_reason').value = '';
    document.getElementById('reset-confirm').checked = false;
    document.getElementById('reset-submit-btn').disabled = true;
}

// Enable/disable submit button based on confirmation checkbox
document.addEventListener('DOMContentLoaded', function() {
    const resetConfirm = document.getElementById('reset-confirm');
    const resetSubmitBtn = document.getElementById('reset-submit-btn');

    if (resetConfirm && resetSubmitBtn) {
        resetConfirm.addEventListener('change', function() {
            resetSubmitBtn.disabled = !this.checked;
        });
    }
});
</script>