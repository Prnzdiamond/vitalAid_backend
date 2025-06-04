@extends('admin.layouts.layout')

@section('title', 'Reports & Analytics')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="text-gray-600 mt-2">Comprehensive platform statistics and insights</p>
    </div>

    <!-- Platform Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalEvents) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Donations</p>
                    <p class="text-2xl font-bold text-gray-900">₦{{ number_format($totalDonations, 2) }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Consultations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalConsultations) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Communities</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCommunities) }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">New Users (30 days)</h3>
                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                    {{ $newUsersLastMonth > 0 ? '+' : '' }}{{ $newUsersLastMonth }}
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($newUsersLastMonth) }}</p>
            <p class="text-sm text-gray-500 mt-1">Users registered in the last 30 days</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">New Events (30 days)</h3>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                    {{ $newEventsLastMonth > 0 ? '+' : '' }}{{ $newEventsLastMonth }}
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($newEventsLastMonth) }}</p>
            <p class="text-sm text-gray-500 mt-1">Events created in the last 30 days</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Donations (30 days)</h3>
                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                    ₦{{ number_format($donationsLastMonth, 2) }}
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900">₦{{ number_format($donationsLastMonth, 2) }}</p>
            <p class="text-sm text-gray-500 mt-1">Total donations in the last 30 days</p>
        </div>
    </div>

    <!-- Quick Actions & Report Links -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Reports</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.reports.user-growth') }}"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">User Growth Statistics</p>
                            <p class="text-sm text-gray-500">Monthly and weekly user registration trends</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('admin.reports.platform-usage') }}"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Platform Usage Metrics</p>
                            <p class="text-sm text-gray-500">Event participation, consultations, community engagement
                            </p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('admin.reports.financial-summary') }}"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Financial Summary</p>
                            <p class="text-sm text-gray-500">Donation trends, categories, and monthly breakdowns</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('admin.reports.verification-stats') }}"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Verification Statistics</p>
                            <p class="text-sm text-gray-500">User verification status and progress by role</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Health</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">User Engagement</span>
                    <div class="flex items-center">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-green-500 h-2 rounded-full"
                                style="width: {{ $totalUsers > 0 ? min(($newUsersLastMonth / $totalUsers) * 100 * 10, 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm text-gray-900">{{ $totalUsers > 0 ? number_format(($newUsersLastMonth /
                            $totalUsers) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Event Activity</span>
                    <div class="flex items-center">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-blue-500 h-2 rounded-full"
                                style="width: {{ $totalEvents > 0 ? min(($newEventsLastMonth / $totalEvents) * 100 * 10, 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm text-gray-900">{{ $totalEvents > 0 ? number_format(($newEventsLastMonth /
                            $totalEvents) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Donation Flow</span>
                    <div class="flex items-center">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-yellow-500 h-2 rounded-full"
                                style="width: {{ $totalDonations > 0 ? min(($donationsLastMonth / $totalDonations) * 100, 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm text-gray-900">{{ $totalDonations > 0 ? number_format(($donationsLastMonth
                            / $totalDonations) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Last updated</span>
                    <span class="text-gray-900">{{ now()->format('M j, Y \a\t g:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection