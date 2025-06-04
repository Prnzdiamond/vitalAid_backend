@extends('admin.layouts.layout')

@section('title', 'Users Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Users Management</h2>
            <p class="text-gray-600">Manage all users in the system</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="document.getElementById('bulk-actions').classList.toggle('hidden')"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                Bulk Actions
            </button>
            <a href="#" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Export Users
            </a>
        </div>
    </div>

    <!-- Bulk Actions Panel (Hidden by default) -->
    <div id="bulk-actions" class="hidden bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <form action="{{ route('admin.users.bulk-action') }}" method="POST">
            @csrf
            <div class="flex items-center space-x-4">
                <select name="action" class="rounded-md border-gray-300 text-sm">
                    <option value="">Select Action</option>
                    <option value="verify">Verify Users</option>
                    <option value="unverify">Remove Verification</option>
                    <option value="delete">Delete Users</option>
                </select>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm">
                    Apply to Selected
                </button>
                <span class="text-sm text-gray-600">Select users below to perform bulk actions</span>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name or email..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Role Filter -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" id="role"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Roles</option>
                        <option value="user" {{ request('role')==='user' ? 'selected' : '' }}>User</option>
                        <option value="health_expert" {{ request('role')==='health_expert' ? 'selected' : '' }}>Health
                            Expert</option>
                        <option value="charity" {{ request('role')==='charity' ? 'selected' : '' }}>Charity</option>
                        <option value="community" {{ request('role')==='community' ? 'selected' : '' }}>Community
                        </option>
                        <option value="admin" {{ request('role')==='admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <!-- Verification Status Filter -->
                <div>
                    <label for="verification_status"
                        class="block text-sm font-medium text-gray-700 mb-1">Verification</label>
                    <select name="verification_status" id="verification_status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="verified" {{ request('verification_status')==='verified' ? 'selected' : '' }}>
                            Verified</option>
                        <option value="pending" {{ request('verification_status')==='pending' ? 'selected' : '' }}>
                            Pending</option>
                        <option value="needs_verification" {{ request('verification_status')==='needs_verification'
                            ? 'selected' : '' }}>Needs Verification</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select name="sort" id="sort"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="created_at" {{ request('sort')==='created_at' ? 'selected' : '' }}>Date Created
                        </option>
                        <option value="first_name" {{ request('sort')==='first_name' ? 'selected' : '' }}>Name</option>
                        <option value="email" {{ request('sort')==='email' ? 'selected' : '' }}>Email</option>
                        <option value="role" {{ request('sort')==='role' ? 'selected' : '' }}>Role</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Apply Filters
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                    Clear All Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Verification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                class="user-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($user->logo)
                                    <img class="h-10 w-10 rounded-full object-cover"
                                        src="{{ asset('storage/' . $user->logo) }}" alt="">
                                    @else
                                    <div
                                        class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    @if($user->phone_number)
                                    <div class="text-xs text-gray-400">{{ $user->phone_number }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($user->role)
                                    @case('admin') bg-purple-100 text-purple-800 @break
                                    @case('health_expert') bg-green-100 text-green-800 @break
                                    @case('charity') bg-blue-100 text-blue-800 @break
                                    @case('community') bg-yellow-100 text-yellow-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(in_array($user->role, ['health_expert', 'charity', 'community']))
                            @if($user->isVerified())
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Verified
                            </span>
                            @elseif($user->verification_status === 'pending')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Pending
                            </span>
                            @elseif($user->verification_status === 'rejected')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Rejected
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Not Verified
                            </span>
                            @endif
                            @else
                            <span class="text-xs text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->location ?? 'Not specified' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}"
                                class="text-indigo-600 hover:text-indigo-900">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="text-green-600 hover:text-green-900">Edit</a>
                            @if($user->role !== 'admin')
                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="{{ $user->is_verified ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                    {{ $user->is_verified ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
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
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $users->simplePaginate() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $users->firstItem() }}</span>
                            to <span class="font-medium">{{ $users->lastItem() }}</span>
                            of <span class="font-medium">{{ $users->total() }}</span> results
                        </p>
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Select all checkbox functionality
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update select-all when individual checkboxes change
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.user-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            const selectAllCheckbox = document.getElementById('select-all');

            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
        });
    });
</script>
@endpush
@endsection