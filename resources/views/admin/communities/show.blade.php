@extends('admin.layouts.layout')

@section('title', 'Community Details')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('admin.communities.index') }}"
        class="inline-flex items-center text-indigo-600 hover:text-indigo-900">
        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Communities
    </a>
</div>

<!-- Community Header -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div
                class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xl font-medium">
                {{ substr($community->first_name, 0, 1) }}
            </div>
            <div class="ml-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ $community->first_name }}</h1>
                <p class="text-gray-600">{{ $community->email }}</p>
                <p class="text-sm text-gray-500">{{ $community->location ?? 'Location not specified' }}</p>
            </div>
        </div>
        <div class="text-right">
            @if($community->verification_status == 'approved')
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
            @elseif($community->verification_status == 'pending')
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
            @elseif($community->verification_status == 'rejected')
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
            @else
            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Unverified</span>
            @endif
            <p class="text-sm text-gray-500 mt-1">Joined {{ $community->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    @if($community->description)
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-gray-700">{{ $community->description }}</p>
    </div>
    @endif
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Members</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $members->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">New Members (30 days)</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $recentMembers }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 rounded-md bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Events</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $events->count() }}</p>
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
                <p class="text-sm font-medium text-gray-600">Upcoming Events</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $upcomingEvents }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Members List -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Community Members</h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
            @forelse($members as $member)
            <div class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div
                            class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-sm font-medium">
                            {{ $member->user ? substr($member->user->first_name, 0, 1) : 'U' }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $member->user ? $member->user->name : 'Unknown User' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Joined {{ $member->joined_at ? $member->joined_at->format('M d, Y') : 'Unknown' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 capitalize">{{ $member->role ?? 'member' }}</span>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                </svg>
                <p class="mt-2">No members found</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Events -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Events</h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
            @forelse($events as $event)
            <div class="px-6 py-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900">{{ $event->title }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($event->description, 80) }}</p>
                        <div class="flex items-center mt-2 text-xs text-gray-500">
                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $event->start_time->format('M d, Y H:i') }}
                        </div>
                        @if($event->location)
                        <div class="flex items-center mt-1 text-xs text-gray-500">
                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $event->location }}
                        </div>
                        @endif
                    </div>
                    <span
                        class="ml-2 px-2 py-1 text-xs font-semibold rounded-full {{ $event->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($event->status ?? 'active') }}
                    </span>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                    </path>
                </svg>
                <p class="mt-2">No events found</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection