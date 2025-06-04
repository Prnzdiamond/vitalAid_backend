@extends('admin.layouts.layout')

@section('title', 'Financial Summary')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Financial Summary</h1>
        <div class="flex space-x-4">
            <form method="GET" class="flex space-x-2">
                <select name="year" class="rounded-md border-gray-300 text-sm">
                    @for($i = 2020; $i <= now()->year; $i++)
                        <option value="{{ $i }}" {{ $year==$i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                </select>
                <select name="month" class="rounded-md border-gray-300 text-sm">
                    <option value="">All Months</option>
                    @for($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ request('month')==$i ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                        @endfor
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                    Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">₦{{ number_format($totalDonations, 2) }}</div>
                    <div class="text-sm text-gray-600">Total Donations</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($donationCount) }}</div>
                    <div class="text-sm text-gray-600">Total Transactions</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">₦{{ number_format($averageDonation, 2) }}</div>
                    <div class="text-sm text-gray-600">Average Donation</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Breakdown -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Breakdown ({{ $year }})</h3>
            </div>
            <div class="p-6">
                @if($monthlyBreakdown->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <p>No donation data available for {{ $year }}.</p>
                </div>
                @else
                <div class="space-y-4">
                    @php
                    $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $maxAmount = $monthlyBreakdown->max('total');
                    @endphp
                    @foreach($monthlyBreakdown as $month)
                    @php
                    $percentage = $maxAmount > 0 ? ($month->total / $maxAmount) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-600">{{ $months[$month->month] }}</span>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">₦{{ number_format($month->total, 2) }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $month->count }} donations</div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Top Categories -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Donation Categories</h3>
            </div>
            <div class="p-6">
                @if($topCategories->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <p>No category data available.</p>
                </div>
                @else
                <div class="space-y-4">
                    @php
                    $maxRequests = $topCategories->max('request_count');
                    @endphp
                    @foreach($topCategories->take(8) as $category)
                    @php
                    $percentage = $maxRequests > 0 ? ($category->request_count / $maxRequests) * 100 : 0;
                    $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500',
                    'bg-pink-500', 'bg-indigo-500', 'bg-gray-500'];
                    $color = $colors[$loop->index % count($colors)];
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-600 capitalize">{{ str_replace('_', ' ',
                                $category->category) }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $category->request_count }}
                                requests</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $color }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection