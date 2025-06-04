@extends('admin.layouts.layout')

@section('title', 'Communities Management')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Communities</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $totalCommunities }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Verified</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $verifiedCommunities }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $pendingVerification }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Members</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $totalMembers }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="verification_status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="approved" {{ request('verification_status')=='approved' ? 'selected' : '' }}>Verified
                    </option>
                    <option value="pending" {{ request('verification_status')=='pending' ? 'selected' : '' }}>Pending
                    </option>
                    <option value="rejected" {{ request('verification_status')=='rejected' ? 'selected' : '' }}>Rejected
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="{{ request('location') }}" placeholder="Location..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Communities Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Community
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($communities as $community)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                {{ substr($community->first_name, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $community->first_name }}</div>
                                <div class="text-sm text-gray-500">{{ $community->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $community->location ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $community->members_count ?? 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $community->events_count ?? 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($community->verification_status == 'approved')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                        @elseif($community->verification_status == 'pending')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif($community->verification_status == 'rejected')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                        @else
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Unverified</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.communities.show', $community->_id) }}"
                            class="text-indigo-600 hover:text-indigo-900">View Details</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No communities found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($communities->hasPages())
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $communities->links() }}
    </div>
    @endif
</div>
@endsection