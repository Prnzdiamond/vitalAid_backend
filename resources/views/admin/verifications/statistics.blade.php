@extends('admin.layouts.layout')

@section('title', 'Verification Statistics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Verification Statistics</h2>
            <p class="text-gray-600">Overview of user verification status across all roles</p>
        </div>
        <div>
            <a href="{{ route('admin.verifications.index') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Verifications
            </a>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="h-4 w-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Review</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Approved</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="h-4 w-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Rejected</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-based Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Health Experts -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Health Experts</h3>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ $stats['health_experts']['total'] }} Total
                </span>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Verified</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full"
                                style="width: {{ $stats['health_experts']['total'] > 0 ? ($stats['health_experts']['verified'] / $stats['health_experts']['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['health_experts']['verified']
                            }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pending</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full"
                                style="width: {{ $stats['health_experts']['total'] > 0 ? ($stats['health_experts']['pending'] / $stats['health_experts']['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['health_experts']['pending'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charities -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Charities</h3>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    {{ $stats['charities']['total'] }} Total
                </span>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Verified</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full"
                                style="width: {{ $stats['charities']['total'] > 0 ? ($stats['charities']['verified'] / $stats['charities']['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['charities']['verified'] }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pending</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full"
                                style="width: {{ $stats['charities']['total'] > 0 ? ($stats['charities']['pending'] / $stats['charities']['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['charities']['pending'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communities -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Communities</h3>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ $stats['communities']['total'] }} Total
                </span>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Verified</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full"
                                style="width: {{ $stats['communities']['total'] > 0 ? ($stats['communities']['verified'] / $stats['communities']['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['communities']['verified'] }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pending</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full"
                                style="width: {{ $stats['communities']['total'] > 0 ? ($stats['communities']['pending'] / $stats['communities']['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['communities']['pending'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Summary by Role</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Verified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pending</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Verification Rate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Health Expert
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            $stats['health_experts']['total'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{
                            $stats['health_experts']['verified'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-medium">{{
                            $stats['health_experts']['pending'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $stats['health_experts']['total'] > 0 ? round(($stats['health_experts']['verified'] /
                            $stats['health_experts']['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Charity
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stats['charities']['total'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{
                            $stats['charities']['verified'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-medium">{{
                            $stats['charities']['pending'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $stats['charities']['total'] > 0 ? round(($stats['charities']['verified'] /
                            $stats['charities']['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                Community
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stats['communities']['total']
                            }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{
                            $stats['communities']['verified'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-medium">{{
                            $stats['communities']['pending'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $stats['communities']['total'] > 0 ? round(($stats['communities']['verified'] /
                            $stats['communities']['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection