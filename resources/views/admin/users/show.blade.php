@extends('admin.layouts.layout')

@section('title', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- User Header -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div
                        class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <div class="flex items-center space-x-4 mt-1">
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
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Not Verified
                            </span>
                            @endif
                            @if($user->verification_status === 'pending')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending Verification
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.edit', $user) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit User
                    </a>
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white
                                       {{ $user->is_verified ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                            {{ $user->is_verified ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
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
                <div>
                    <dt class="text-sm font-medium text-gray-500">Joined</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                </div>
                @if($user->verification_approved_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Verified At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->verification_approved_at->format('M d, Y') }}</dd>
                </div>
                @endif
                @if($user->description)
                <div class="sm:col-span-2 lg:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->description }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @if(isset($userStats['consultations_requested']))
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Consultations Requested</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $userStats['consultations_requested'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($userStats['consultations_handled']))
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Consultations Handled</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $userStats['consultations_handled'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($userStats['donations_made']))
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Donations Made</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $userStats['donations_made'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($userStats['events_joined']))
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Events Joined</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $userStats['events_joined'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($userStats['community_members']))
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Community Members</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $userStats['community_members'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($userStats['average_rating']) && $userStats['average_rating'] > 0)
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Rating</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($userStats['average_rating'],
                                1) }}/5</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Activity Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Consultations -->
        @if(isset($activityData['recent_consultations']) && $activityData['recent_consultations']->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Consultations</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($activityData['recent_consultations']->take(5) as $consultation)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                @if($consultation->doctor)
                                With {{ $consultation->doctor->name }}
                                @else
                                AI Consultation
                                @endif
                            </p>
                            <p class="text-sm text-gray-500">{{ $consultation->status }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-900">{{ $consultation->last_message_at->format('M d, Y') }}</p>
                            @if($consultation->rating)
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++) <svg
                                    class="h-4 w-4 {{ $i <= $consultation->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    @endfor
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Donations -->
        @if(isset($activityData['recent_donations']) && $activityData['recent_donations']->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Donations</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($activityData['recent_donations']->take(5) as $donation)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                ₦{{ number_format($donation->amount, 2) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                @if($donation->donationRequest)
                                To {{ $donation->donationRequest->title }}
                                @else
                                Donation Request Not Found
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-900">{{ $donation->created_at->format('M d, Y') }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($donation->payment_status === 'completed') bg-green-100 text-green-800
                                @elseif($donation->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($donation->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Events -->
        @if(isset($activityData['recent_events']) && $activityData['recent_events']->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Events</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($activityData['recent_events']->take(5) as $eventParticipant)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $eventParticipant->event->title ?? 'Event Title' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $eventParticipant->event->location ?? 'Location TBD' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-900">
                                {{ $eventParticipant->event->start_time ?
                                $eventParticipant->event->start_time->format('M d, Y') : 'TBD' }}
                            </p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($eventParticipant->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($eventParticipant->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($eventParticipant->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Verification Section -->
    @if(in_array($user->role, ['health_expert', 'charity', 'community']))
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Verification Status</h3>
        </div>
        <div class="px-6 py-4">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">Verification Progress</span>
                    <span class="text-sm text-gray-500">{{ $user->verification_progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $user->verification_progress }}%">
                    </div>
                </div>

                @if($user->verification_status === 'rejected' && $user->verification_rejection_reason)
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Verification Rejected</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ $user->verification_rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($user->verification_documents && count($user->verification_documents) > 0)
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Uploaded Documents</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($user->document_urls as $docType => $docInfo)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $user->required_documents[$docType] ?? ucfirst(str_replace('_', ' ',
                                        $docType)) }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $docInfo['filename'] }}</p>
                                </div>
                                <a href="{{ $docInfo['url'] }}" target="_blank"
                                    class="text-indigo-600 hover:text-indigo-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Back Button -->
    <div class="flex justify-start">
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Back to Users
        </a>
    </div>
</div>
@endsection