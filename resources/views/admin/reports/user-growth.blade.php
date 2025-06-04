@extends('admin.layouts.layout')

@section('title', 'User Growth Statistics')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">User Growth Statistics</h1>
        <div class="flex space-x-4">
            <form method="GET" class="flex space-x-2">
                <select name="period" class="rounded-md border-gray-300 text-sm">
                    <option value="monthly" {{ $period==='monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="weekly" {{ $period==='weekly' ? 'selected' : '' }}>Weekly</option>
                </select>
                <select name="year" class="rounded-md border-gray-300 text-sm">
                    @for($i = 2020; $i <= now()->year; $i++)
                        <option value="{{ $i }}" {{ $year==$i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                    Filter
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">User Registration Trends - {{ ucfirst($period) }} ({{ $year }})</h2>

        @if($data->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <p>No data available for the selected period.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $period === 'monthly' ? 'Month' : 'Week' }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Users
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Health Experts
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Charities
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Communities
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                    $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    @endphp
                    @foreach($data as $periodNum => $periodData)
                    @php
                    $userCount = $periodData->where('role', 'user')->sum('count');
                    $healthExpertCount = $periodData->where('role', 'health_expert')->sum('count');
                    $charityCount = $periodData->where('role', 'charity')->sum('count');
                    $communityCount = $periodData->where('role', 'community')->sum('count');
                    $total = $userCount + $healthExpertCount + $charityCount + $communityCount;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $period === 'monthly' ? $months[$periodNum] : 'Week ' . $periodNum }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $userCount }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $healthExpertCount }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $charityCount }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $communityCount }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ $total }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
            $totalUsers = $data->flatten()->where('role', 'user')->sum('count');
            $totalHealthExperts = $data->flatten()->where('role', 'health_expert')->sum('count');
            $totalCharities = $data->flatten()->where('role', 'charity')->sum('count');
            $totalCommunities = $data->flatten()->where('role', 'community')->sum('count');
            @endphp

            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $totalUsers }}</div>
                <div class="text-sm text-blue-600">Total Users</div>
            </div>

            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $totalHealthExperts }}</div>
                <div class="text-sm text-green-600">Health Experts</div>
            </div>

            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">{{ $totalCharities }}</div>
                <div class="text-sm text-purple-600">Charities</div>
            </div>

            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $totalCommunities }}</div>
                <div class="text-sm text-yellow-600">Communities</div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection