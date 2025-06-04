@extends('admin.layouts.layout')

@section('title', 'Edit User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Edit User: {{ $user->name }}</h2>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($user->role === 'admin') bg-purple-100 text-purple-800
                        @elseif($user->role === 'health_expert') bg-green-100 text-green-800
                        @elseif($user->role === 'charity') bg-blue-100 text-blue-800
                        @elseif($user->role === 'community') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                    @if($user->is_verified)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Verified
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="px-6 py-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- First Name -->
                        <div class="sm:col-span-3">
                            <label for="first_name" class="block text-sm font-medium text-gray-700">
                                {{ in_array($user->role, ['charity', 'community']) ? 'Organization Name' : 'First Name'
                                }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" name="first_name" id="first_name"
                                    value="{{ old('first_name', $user->first_name) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('first_name') border-red-300 @enderror"
                                    required>
                            </div>
                            @error('first_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        @if(!in_array($user->role, ['charity', 'community']))
                        <div class="sm:col-span-3">
                            <label for="last_name" class="block text-sm font-medium text-gray-700">
                                Last Name
                            </label>
                            <div class="mt-1">
                                <input type="text" name="last_name" id="last_name"
                                    value="{{ old('last_name', $user->last_name) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('last_name') border-red-300 @enderror">
                            </div>
                            @error('last_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Email -->
                        <div class="sm:col-span-3">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('email') border-red-300 @enderror"
                                    required>
                            </div>
                            @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="sm:col-span-3">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">
                                Phone Number
                            </label>
                            <div class="mt-1">
                                <input type="text" name="phone_number" id="phone_number"
                                    value="{{ old('phone_number', $user->phone_number) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('phone_number') border-red-300 @enderror">
                            </div>
                            @error('phone_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div class="sm:col-span-3">
                            <label for="role" class="block text-sm font-medium text-gray-700">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <select name="role" id="role"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('role') border-red-300 @enderror"
                                    required>
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : ''
                                        }}>User</option>
                                    <option value="health_expert" {{ old('role', $user->role) === 'health_expert' ?
                                        'selected' : '' }}>Health Expert</option>
                                    <option value="charity" {{ old('role', $user->role) === 'charity' ? 'selected' : ''
                                        }}>Charity</option>
                                    <option value="community" {{ old('role', $user->role) === 'community' ? 'selected' :
                                        '' }}>Community</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : ''
                                        }}>Admin</option>
                                </select>
                            </div>
                            @error('role')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="sm:col-span-3">
                            <label for="location" class="block text-sm font-medium text-gray-700">
                                Location
                            </label>
                            <div class="mt-1">
                                <input type="text" name="location" id="location"
                                    value="{{ old('location', $user->location) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('location') border-red-300 @enderror">
                            </div>
                            @error('location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="sm:col-span-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                {{ in_array($user->role, ['charity', 'community']) ? 'About Organization' :
                                'Description' }}
                            </label>
                            <div class="mt-1">
                                <textarea name="description" id="description" rows="4"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('description') border-red-300 @enderror"
                                    placeholder="{{ in_array($user->role, ['charity', 'community']) ? 'Tell us about your organization, mission, and goals...' : 'Brief description about yourself...' }}">{{ old('description', $user->description) }}</textarea>
                            </div>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Verification Status -->
                @if(in_array($user->role, ['health_expert', 'charity', 'community']))
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Verification Status</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Is Verified -->
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_verified" id="is_verified" value="1" {{
                                    old('is_verified', $user->is_verified) ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <label for="is_verified" class="ml-2 block text-sm text-gray-900">
                                    User is verified
                                </label>
                            </div>
                            @error('is_verified')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Verification Status -->
                        <div class="sm:col-span-3">
                            <label for="verification_status" class="block text-sm font-medium text-gray-700">
                                Verification Status
                            </label>
                            <div class="mt-1">
                                <select name="verification_status" id="verification_status"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('verification_status') border-red-300 @enderror">
                                    <option value="">None</option>
                                    <option value="pending" {{ old('verification_status', $user->verification_status)
                                        === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('verification_status', $user->verification_status)
                                        === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('verification_status', $user->verification_status)
                                        === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            @error('verification_status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Verification Progress Display -->
                        @if($user->verification_progress !== null)
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Document Upload Progress
                            </label>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-indigo-600 h-2 rounded-full"
                                        style="width: {{ $user->verification_progress }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $user->verification_progress }}%</span>
                            </div>
                            @if($user->verification_progress < 100) <p class="mt-1 text-sm text-yellow-600">
                                Missing documents: {{ implode(', ', array_values($user->getMissingDocuments())) }}
                                </p>
                                @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Professional Information (Health Experts only) -->
                @if($user->role === 'health_expert')
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Professional Information</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Specialization -->
                        <div class="sm:col-span-3">
                            <label for="specialization" class="block text-sm font-medium text-gray-700">
                                Specialization
                            </label>
                            <div class="mt-1">
                                <input type="text" name="specialization" id="specialization"
                                    value="{{ old('specialization', $user->specialization) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Experience Years -->
                        <div class="sm:col-span-3">
                            <label for="experience_years" class="block text-sm font-medium text-gray-700">
                                Years of Experience
                            </label>
                            <div class="mt-1">
                                <input type="number" name="experience_years" id="experience_years" min="0"
                                    value="{{ old('experience_years', $user->experience_years) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Registration Number -->
                        <div class="sm:col-span-3">
                            <label for="registration_number" class="block text-sm font-medium text-gray-700">
                                Professional Registration Number
                            </label>
                            <div class="mt-1">
                                <input type="text" name="registration_number" id="registration_number"
                                    value="{{ old('registration_number', $user->registration_number) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Qualifications -->
                        <div class="sm:col-span-6">
                            <label for="qualifications" class="block text-sm font-medium text-gray-700">
                                Qualifications
                            </label>
                            <div class="mt-1">
                                <textarea name="qualifications" id="qualifications" rows="3"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="List your educational qualifications and certifications...">{{ old('qualifications', $user->qualifications) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Organization Information (Charity/Community only) -->
                @if(in_array($user->role, ['charity', 'community']))
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                        {{ $user->role === 'charity' ? 'Organization' : 'Community' }} Information
                    </h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Website -->
                        <div class="sm:col-span-3">
                            <label for="website" class="block text-sm font-medium text-gray-700">
                                Website
                            </label>
                            <div class="mt-1">
                                <input type="url" name="website" id="website"
                                    value="{{ old('website', $user->website) }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="https://example.com">
                            </div>
                        </div>

                        <!-- Founding Date (Charity only) -->
                        @if($user->role === 'charity')
                        <div class="sm:col-span-3">
                            <label for="founding_date" class="block text-sm font-medium text-gray-700">
                                Founding Date
                            </label>
                            <div class="mt-1">
                                <input type="date" name="founding_date" id="founding_date"
                                    value="{{ old('founding_date', $user->founding_date ? $user->founding_date->format('Y-m-d') : '') }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                        @endif

                        <!-- Mission Statement -->
                        <div class="sm:col-span-6">
                            <label for="mission_statement" class="block text-sm font-medium text-gray-700">
                                {{ $user->role === 'charity' ? 'Mission Statement' : 'Community Purpose' }}
                            </label>
                            <div class="mt-1">
                                <textarea name="mission_statement" id="mission_statement" rows="3"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="{{ $user->role === 'charity' ? 'Describe your organization\'s mission and goals...' : 'Describe your community\'s purpose and activities...' }}">{{ old('mission_statement', $user->mission_statement) }}</textarea>
                            </div>
                        </div>

                        <!-- Target Audience -->
                        <div class="sm:col-span-6">
                            <label for="target_audience" class="block text-sm font-medium text-gray-700">
                                Target Audience
                            </label>
                            <div class="mt-1">
                                <textarea name="target_audience" id="target_audience" rows="2"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Who do you serve or aim to help?">{{ old('target_audience', $user->target_audience) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.users.show', $user) }}"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit"
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update User
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Additional Actions -->
    <div class="mt-6 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Additional Actions</h3>
        </div>
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-3">
                <!-- Toggle Verification Status -->
                @if(in_array($user->role, ['health_expert', 'charity', 'community']))
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white
                                   {{ $user->is_verified ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $user->is_verified ? 'Revoke Verification' : 'Grant Verification' }}
                    </button>
                </form>
                @endif

                <!-- Reset Verification -->
                @if(in_array($user->role, ['health_expert', 'charity', 'community']) && ($user->verification_documents
                || $user->verification_status))
                <button type="button" onclick="confirmResetVerification()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset Verification
                </button>
                @endif

                <!-- Delete User -->
                <button type="button" onclick="confirmDelete()"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    Delete User
                </button>
            </div>

            <!-- Warning Messages -->
            <div class="mt-4 space-y-2">
                @if($user->consultationsHandled()->exists() || $user->donationRequests()->exists() ||
                $user->createdEvents()->exists())
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Warning:</strong> This user has active consultations, donation requests, or
                                events. Deletion may not be possible.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Delete User</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete <strong>{{ $user->name }}</strong>? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDelete"
                    class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Delete
                </button>
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Verification Modal -->
<div id="resetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Reset Verification</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    This will reset all verification documents and status for <strong>{{ $user->name }}<p
                            class="text-sm text-gray-500">
                            This will reset all verification documents and status for <strong>{{ $user->name
                                }}</strong>. They will need to
                            re-upload all documents.
                        </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmReset"
                    class="px-4 py-2 bg-yellow-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                    Reset
                </button>
                <button onclick="closeResetModal()"
                    class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Delete Modal Functions
                    function confirmDelete() {
                        document.getElementById('deleteModal').classList.remove('hidden');
                    }

                    function closeDeleteModal() {
                        document.getElementById('deleteModal').classList.add('hidden');
                    }

                    document.getElementById('confirmDelete').addEventListener('click', function() {
                        // Create and submit delete form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("admin.users.destroy", $user) }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';

                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        document.body.appendChild(form);
                        form.submit();
                    });

                    // Reset Verification Modal Functions
                    function confirmResetVerification() {
                        document.getElementById('resetModal').classList.remove('hidden');
                    }

                    function closeResetModal() {
                        document.getElementById('resetModal').classList.add('hidden');
                    }

                    document.getElementById('confirmReset').addEventListener('click', function() {
                        // Create and submit reset form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("admin.verifications.reset", $user) }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'PATCH';

                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        document.body.appendChild(form);
                        form.submit();
                    });

                    // Role-based field visibility
                    document.getElementById('role').addEventListener('change', function() {
                        const role = this.value;
                        const lastName = document.querySelector('[name="last_name"]').closest('.sm\\:col-span-3');
                        const firstNameLabel = document.querySelector('label[for="first_name"]');
                        const descriptionLabel = document.querySelector('label[for="description"]');
                        const descriptionPlaceholder = document.getElementById('description');

                        // Update labels and visibility based on role
                        if (role === 'charity' || role === 'community') {
                            firstNameLabel.innerHTML = 'Organization Name <span class="text-red-500">*</span>';
                            if (lastName) lastName.style.display = 'none';
                            descriptionLabel.textContent = 'About Organization';
                            descriptionPlaceholder.placeholder = 'Tell us about your organization, mission, and goals...';
                        } else {
                            firstNameLabel.innerHTML = 'First Name <span class="text-red-500">*</span>';
                            if (lastName) lastName.style.display = 'block';
                            descriptionLabel.textContent = 'Description';
                            descriptionPlaceholder.placeholder = 'Brief description about yourself...';
                        }
                    });

                    // Form validation
                    document.querySelector('form').addEventListener('submit', function(e) {
                        const isVerified = document.getElementById('is_verified').checked;
                        const verificationStatus = document.getElementById('verification_status').value;

                        // If user is marked as verified, ensure verification status is approved
                        if (isVerified && verificationStatus !== 'approved') {
                            e.preventDefault();
                            alert('If a user is verified, their verification status must be set to "Approved".');
                            return false;
                        }

                        // If verification status is approved, ensure user is marked as verified
                        if (verificationStatus === 'approved' && !isVerified) {
                            e.preventDefault();
                            alert('If verification status is "Approved", the user must be marked as verified.');
                            return false;
                        }
                    });

                    // Close modals when clicking outside
                    window.addEventListener('click', function(event) {
                        const deleteModal = document.getElementById('deleteModal');
                        const resetModal = document.getElementById('resetModal');

                        if (event.target === deleteModal) {
                            closeDeleteModal();
                        }
                        if (event.target === resetModal) {
                            closeResetModal();
                        }
                    });

                    // Close modals with Escape key
                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') {
                            closeDeleteModal();
                            closeResetModal();
                        }
                    });
</script>

@endsection