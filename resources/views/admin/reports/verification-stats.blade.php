@extends('admin.layouts.layout')

@section('title', 'Verification Statistics')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Verification Statistics</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.users.index') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                Manage Users
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($verificationStats['total_pending'])
                        }}</div>
                    <div class="text-sm text-gray-600">Pending Review</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($verificationStats['total_approved'])
                        }}</div>
                    <div class="text-sm text-gray-600">Approved</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($verificationStats['total_rejected'])
                        }}</div>
                    <div class="text-sm text-gray-600">Rejected</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($verificationStats['total_pending'] +
                        $verificationStats['total_approved'] + $verificationStats['total_rejected']) }}</div>
                    <div class="text-sm text-gray-600">Total Requests</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-based Verification Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Health Experts</h3>
                <div class="p-2 rounded-full bg-green-100">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-2">{{
                number_format($verificationStats['health_experts_verified']) }}</div>
            <div class="text-sm text-gray-600">Verified Health Experts</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Charities</h3>
                <div class="p-2 rounded-full bg-purple-100">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-2">{{
                number_format($verificationStats['charities_verified']) }}</div>
            <div class="text-sm text-gray-600">Verified Charities</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Communities</h3>
                <div class="p-2 rounded-full bg-blue-100">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-2">{{
                number_format($verificationStats['communities_verified']) }}</div>
            <div class="text-sm text-gray-600">Verified Communities</div>
        </div>
    </div>

    <!-- Detailed Breakdown by Role -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Verification Status by Role</h3>
        </div>
        <div class="p-6">
            @if($verificationByRole->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <p>No verification data available.</p>
            </div>
            @else
            <div class="space-y-6">
                @foreach(['health_expert' => 'Health Experts', 'charity' => 'Charities', 'community' => 'Communities']
                as $role => $label)
                @php
                $roleData = $verificationByRole->get($role, collect());
                $pending = $roleData->where('verification_status', 'pending')->first()->count ?? 0;
                $approved = $roleData->where('verification_status', 'approved')->first()->count ?? 0;
                $rejected = $roleData->where('verification_status', 'rejected')->first()->count ?? 0;
                $total = $pending + $approved + $rejected;
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-md font-medium text-gray-900">{{ $label }}</h4>
                        <span class="text-sm text-gray-600">{{ $total }} applications</span>
                    </div>

                    @if($total > 0)
                    <div class="grid grid-cols-3 gap-4 mb-3">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-yellow-600">{{ $pending }}</div>
                            <div class="text-xs text-gray-500">Pending</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-green-600">{{ $approved }}</div>
                            <div class="text-xs text-gray-500">Approved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-red-600">{{ $rejected }}</div>
                            <div class="text-xs text-gray-500">Rejected</div>
                        </div>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-3 flex overflow-hidden">
                        @if($pending > 0)
                        <div class="bg-yellow-500 h-full" style="width: {{ ($pending / $total) * 100 }}%"></div>
                        @endif
                        @if($approved > 0)
                        <div class="bg-green-500 h-full" style="width: {{ ($approved / $total) * 100 }}%"></div>
                        @endif
                        @if($rejected > 0)
                        <div class="bg-red-500 h-full" style="width: {{ ($rejected / $total) * 100 }}%"></div>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-4 text-gray-400">
                        <p>No applications for {{ strtolower($label) }}</p>
                    </div>
                    @endif
                </div>

                @if(!$loop->last)
                <hr class="border-gray-200">
                @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index', ['verification_status' => 'pending']) }}"
                    class="flex items-center justify-center px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-yellow-700 font-medium">Review Pending</span>
                </a>

                <a href="{{ route('admin.users.index', ['verification_status' => 'approved']) }}"
                    class="flex items-center justify-center px-4 py-3 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-700 font-medium">View Approved</span>
                </a>

                <a href="{{ route('admin.users.index', ['verification_status' => 'rejected']) }}"
                    class="flex items-center justify-center px-4 py-3 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-red-700 font-medium">View Rejected</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection