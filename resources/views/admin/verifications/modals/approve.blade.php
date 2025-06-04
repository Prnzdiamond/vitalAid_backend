<!-- Approve Verification Modal -->
<div id="approve-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-semibold text-gray-900">Approve Verification</h3>
                <button onclick="hideModal('approve-modal')" class="text-gray-400 hover:text-gray-600">
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
                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Approve verification for:</h4>
                        <p class="text-sm text-gray-600" id="approve-user-name"></p>
                    </div>
                </div>

                <form id="approve-form" method="POST" action="">
                    @csrf
                    <input type="hidden" id="approve-user-id" name="user_id" value="">

                    <div class="mb-4">
                        <label for="approval_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Approval Notes (Optional)
                        </label>
                        <textarea id="approval_notes" name="approval_notes" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                            placeholder="Add any additional notes about this approval..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters</p>
                    </div>

                    <!-- Warning Message -->
                    <div class="bg-green-50 border border-green-200 rounded-md p-3 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800">
                                    <strong>This action will:</strong>
                                </p>
                                <ul class="text-sm text-green-700 mt-1 space-y-1">
                                    <li>• Mark the user as verified</li>
                                    <li>• Grant access to verification-required features</li>
                                    <li>• Send a confirmation notification to the user</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="hideModal('approve-modal')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                            <svg class="inline-block mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Approve Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Update form action when modal is shown
function showApproveModal(userId, userName) {
    document.getElementById('approve-user-id').value = userId;
    document.getElementById('approve-user-name').textContent = userName;
    document.getElementById('approve-form').action = `/admin/verifications/${userId}/approve`;
    document.getElementById('approve-modal').classList.remove('hidden');
    // Clear previous notes
    document.getElementById('approval_notes').value = '';
}
</script>