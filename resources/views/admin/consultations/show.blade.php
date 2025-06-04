@extends('admin.layouts.layout')

@section('title', 'Consultation Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.consultations.index') }}"
                class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Consultations
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Consultation #{{ $consultation->id }}</h1>
        </div>
        <div class="text-right">
            <span
                class="px-3 py-1 text-sm font-semibold rounded-full
                {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' :
                   ($consultation->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
            </span>
            @if($consultation->follow_up_requested)
            <span class="ml-2 px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">Follow-up
                Requested</span>
            @endif
        </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Consultations</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.consultations.analytics') }}"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                📊 Analytics
            </a>
            <a href="{{ route('admin.consultations.doctor-performance') }}"
                class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md">
                👨‍⚕️ Doctor Performance
            </a>
            <a href="{{ route('admin.consultations.follow-up-requests') }}"
                class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md">
                📋 Follow-up Requests
            </a>
        </div>
    </div>

    <!-- Consultation Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Patient Information</h3>
            @if($consultation->user)
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500">Name:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->user->name }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Email:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->user->email }}</span>
                </div>
                @if($consultation->user->phone_number)
                <div>
                    <span class="text-sm font-medium text-gray-500">Phone:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->user->phone_number }}</span>
                </div>
                @endif
                @if($consultation->user->location)
                <div>
                    <span class="text-sm font-medium text-gray-500">Location:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->user->location }}</span>
                </div>
                @endif
            </div>
            @else
            <p class="text-sm text-gray-500">User information not available</p>
            @endif
        </div>

        <!-- Doctor Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Healthcare Provider</h3>
            @if($consultation->doctor)
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500">Name:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->doctor->name }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Email:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->doctor->email }}</span>
                </div>
                @if($consultation->doctor->specialization)
                <div>
                    <span class="text-sm font-medium text-gray-500">Specialization:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->doctor->specialization }}</span>
                </div>
                @endif
                @if($consultation->doctor->experience_years)
                <div>
                    <span class="text-sm font-medium text-gray-500">Experience:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $consultation->doctor->experience_years }} years</span>
                </div>
                @endif
            </div>
            @else
            <div class="flex items-center">
                <div class="p-2 rounded-md bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">AI Assistant</p>
                    <p class="text-sm text-gray-500">Handled by AI system</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Timeline & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Messages/Conversation -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Conversation</h3>
            </div>
            <div class="p-6">
                @if($consultation->messages && count($consultation->messages) > 0)
                <div class="space-y-4">
                    @foreach($consultation->messages as $message)
                    <div class="flex {{ $message['sender'] === 'user' ? 'justify-start' : 'justify-end' }}">
                        <div
                            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message['sender'] === 'user' ? 'bg-gray-100' : 'bg-blue-500 text-white' }}">
                            <div
                                class="text-xs {{ $message['sender'] === 'user' ? 'text-gray-500' : 'text-blue-100' }} mb-1">
                                {{ $message['sender'] === 'user' ? 'Patient' : ($consultation->doctor ?
                                $consultation->doctor->name : 'AI Assistant') }}
                                @if(isset($message['timestamp']))
                                • {{ \Carbon\Carbon::parse($message['timestamp'])->format('M j, H:i') }}
                                @endif
                            </div>
                            <div class="text-sm">{{ $message['message'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-8">No messages in this consultation</p>
                @endif
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Consultation Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Created:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ $consultation->created_at->format('M j, Y H:i')
                            }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Last Activity:</span>
                        <span class="ml-2 text-sm text-gray-900">
                            {{ $consultation->last_message_at ? $consultation->last_message_at->format('M j, Y H:i') :
                            'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Handled By:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ ucfirst($consultation->handled_by ?? 'AI') }}</span>
                    </div>
                </div>
            </div>

            <!-- Rating -->
            @if($consultation->rating)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Rating</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $consultation->rating }}/5</span>
                        <div class="flex ml-2">
                            @for($i = 1; $i <= 5; $i++) <svg
                                class="h-5 w-5 {{ $i <= $consultation->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                @endfor
                        </div>
                    </div>
                    @if($consultation->rating_comment)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Comment:</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $consultation->rating_comment }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Follow-up Request -->
            @if($consultation->follow_up_requested)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Follow-up Request</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Requested by:</span>
                        <span class="ml-2 text-sm text-gray-900">
                            {{ $consultation->followUpRequestedBy->name ?? 'Unknown' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Requested on:</span>
                        <span class="ml-2 text-sm text-gray-900">
                            {{ $consultation->follow_up_requested_at ? $consultation->follow_up_requested_at->format('M
                            j, Y H:i') : 'N/A' }}
                        </span>
                    </div>
                    @if($consultation->follow_up_reason)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Reason:</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $consultation->follow_up_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Memory/Notes -->
            @if($consultation->memory)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes/Memory</h3>
                <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $consultation->memory }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection