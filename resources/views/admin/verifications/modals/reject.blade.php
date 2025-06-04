<!-- Reject Verification Modal -->
<div id="reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-semibold text-gray-900">Reject Verification</h3>
                <button onclick="hideModal('reject-modal')" class="text-gray-400 hover:text-gray-600">
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
                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Reject verification for:</h4>
                        <p class="text-sm text-gray-600" id="reject-user-name"></p>
                    </div>
                </div>

                <form id="reject-form" method="POST" action="">
                    @csrf
                    <input type="hidden" id="reject-user-id" name="user_id" value="">

                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                            placeholder="Please provide a clear reason for rejecting this verification. This will be sent to the user so they can address the issues and resubmit."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters. Be specific about what needs to
                            be corrected.</p>
                    </div>

                    <!-- Common Rejection Reasons -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Common Reasons (Click to use)
                        </label>
                        <div class="space-y-2">
                            <button type="button"
                                onclick="setRejectionReason('Documents are unclear or unreadable. Please upload clearer images or scans.')"
                                class="w-full text-left text-xs px-3 py-2 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 transition-colors">
                                Documents are unclear or unreadable
                            </button>
                            <button type="button"
                                onclick="setRejectionReason('Required documents are missing or incomplete. Please ensure all mandatory documents are uploaded.')"
                                class="w-full text-left text-xs px-3 py-2 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 transition-colors">
                                Missing or incomplete documents
                            </button>
                            <button type="button"
                                onclick="setRejectionReason('Submitted documents do not meet our verification requirements or appear to be invalid.')"
                                class="w-full text-left text-xs px-3 py-2 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 transition-colors">
                                Documents do not meet requirements
                            </button>
                            <button type="button"
                                onclick="setRejectionReason('Information provided does not match the submitted documents. Please ensure consistency across all submissions.')"
                                class="w-full text-left text-xs px-3 py-2 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 transition-colors">
                                Information mismatch
                            </button>
                        </div>
                    </div>

                    <!-- Warning Message -->
                    <div class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-800">
                                    <strong>This action will:</strong>
                                </p>
                                <ul class="text-sm text-red-700 mt-1 space-y-1">
                                    <li>• Mark the verification as rejected</li>
                                    <li>• Send the rejection reason to the user</li>
                                    <li>• Allow the user to resubmit with corrections</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="hideModal('reject-modal')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                            <svg class="inline-block mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const rejectRouteBase = @json(route('admin.verifications.reject', 'REPLACE_ID'));
function showRejectModal(userId, userName) {
const rejectUrl = rejectRouteBase.replace('REPLACE_ID', userId);
console.log('Reject URL:', rejectUrl);
document.getElementById('reject-user-id').value = userId;
document.getElementById('reject-form').action = rejectUrl;
document.getElementById('reject-user-name').textContent = userName;
document.getElementById('rejection_reason').value = '';
document.getElementById('reject-modal').classList.remove('hidden');
}

// Set rejection reason from common reasons
function setRejectionReason(reason) {
    document.getElementById('rejection_reason').value = reason;
}
</script>