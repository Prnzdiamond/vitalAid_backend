@extends('admin.layouts.layout')

@section('title', 'Platform Usage Metrics')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Platform Usage Metrics</h1>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $dailyActiveUsers }}</div>
                    <div class="text-sm text-gray-600">Daily Active Users</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $eventParticipation->total_participants ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600">Event Participants (30 days)</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $consultationMetrics['total'] }}</div>
                    <div class="text-sm text-gray-600">Total Consultations</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $communityEngagement->total_members ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Community Members</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Consultation Metrics -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Consultation Metrics</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Consultations</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $consultationMetrics['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Completed</span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $consultationMetrics['completed'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Active</span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $consultationMetrics['active'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Average Rating</span>
                        <div class="flex items-center">
                            <span class="text-sm font-semibold text-gray-900">{{
                                number_format($consultationMetrics['average_rating'], 1) }}</span>
                            <div class="flex ml-2">
                                @for($i = 1; $i <= 5; $i++) <svg
                                    class="w-4 h-4 {{ $i <= $consultationMetrics['average_rating'] ? 'text-yellow-400' : 'text-gray-300' }}"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    @endfor
                            </div>
                        </div>
                    </div>
                </div>

                @if($consultationMetrics['total'] > 0)
                <div class="mt-6">
                    <div class="text-sm text-gray-600 mb-2">Completion Rate</div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                        $completionRate = ($consultationMetrics['completed'] / $consultationMetrics['total']) * 100;
                        @endphp
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($completionRate, 1) }}%</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Engagement Overview -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Engagement Overview</h3>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Event Participation</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $eventParticipation->total_participants
                                ?? 0 }}</span>
                        </div>
                        <div class="text-xs text-gray-500">Last 30 days</div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Active Community Members</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $communityEngagement->total_members ??
                                0 }}</span>
                        </div>
                        <div class="text-xs text-gray-500">Currently active</div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Daily Active Users</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $dailyActiveUsers }}</span>
                        </div>
                        <div class="text-xs text-gray-500">Last 24 hours</div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm font-medium text-gray-900 mb-2">Platform Health</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-lg font-bold text-green-600">
                                {{ $consultationMetrics['total'] > 0 ? number_format(($consultationMetrics['completed']
                                / $consultationMetrics['total']) * 100, 1) : 0 }}%
                            </div>
                            <div class="text-xs text-gray-600">Success Rate</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-blue-600">{{
                                number_format($consultationMetrics['average_rating'], 1) }}</div>
                            <div class="text-xs text-gray-600">Avg Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection