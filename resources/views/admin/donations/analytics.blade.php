@extends('admin.layouts.layout')

@section('title', 'Donation Analytics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Donation Analytics</h1>
        <a href="{{ route('admin.donations.index') }}"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            Back to Donations
        </a>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Amount Raised</dt>
                            <dd class="text-lg font-medium text-gray-900">₦{{ number_format($totalAmount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Donations</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($totalDonations) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Donation</dt>
                            <dd class="text-lg font-medium text-gray-900">₦{{ number_format($averageDonation, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Donation Status Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($statusBreakdown as $status)
            <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold
                    @if($status->status === 'success') text-green-600
                    @elseif($status->status === 'pending') text-yellow-600
                    @else text-red-600 @endif">
                    {{ number_format($status->count) }}
                </div>
                <div class="text-sm text-gray-500 uppercase">{{ ucfirst($status->status) }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Donation Trends ({{ now()->year }})</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($monthlyData as $data)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ DateTime::createFromFormat('!m', $data->month)->format('F') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data->count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₦{{ number_format($data->total, 2)
                            }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Donors -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Donors</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Donated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topDonors as $donor)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $donor->user->name ?? 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">{{ $donor->user->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            number_format($donor->donation_count) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₦{{
                            number_format($donor->total_donated, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No donors found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection