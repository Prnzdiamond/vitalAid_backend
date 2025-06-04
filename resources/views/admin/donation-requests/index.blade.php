@extends('admin.layouts.layout')

@section('title', 'Donation Requests')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($totalRequests) }}</div>
            <div class="text-sm text-gray-600">Total Requests</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600">{{ number_format($activeRequests) }}</div>
            <div class="text-sm text-gray-600">Active</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-gray-600">{{ number_format($completedRequests) }}</div>
            <div class="text-sm text-gray-600">Completed</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-red-600">{{ number_format($urgentRequests) }}</div>
            <div class="text-sm text-gray-600">Urgent</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-indigo-600">₦{{ number_format($totalAmountNeeded) }}</div>
            <div class="text-sm text-gray-600">Amount Needed</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-purple-600">₦{{ number_format($totalAmountRaised) }}</div>
            <div class="text-sm text-gray-600">Amount Raised</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status')==='paused' ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ request('status')==='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status')==='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <select name="category"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    <option value="medical" {{ request('category')==='medical' ? 'selected' : '' }}>Medical</option>
                    <option value="education" {{ request('category')==='education' ? 'selected' : '' }}>Education
                    </option>
                    <option value="emergency" {{ request('category')==='emergency' ? 'selected' : '' }}>Emergency
                    </option>
                    <option value="community" {{ request('category')==='community' ? 'selected' : '' }}>Community
                    </option>
                </select>
            </div>
            <div>
                <select name="is_urgent"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Urgency</option>
                    <option value="1" {{ request('is_urgent')==='1' ? 'selected' : '' }}>Urgent</option>
                    <option value="0" {{ request('is_urgent')==='0' ? 'selected' : '' }}>Not Urgent</option>
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
            </div>
        </form>
    </div>

    <!-- Donation Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Request</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Organization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($donationRequests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($request->banner_url)
                                <img class="h-10 w-10 rounded-lg object-cover mr-3"
                                    src="{{ asset('storage/' . $request->banner_url) }}" alt="">
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ Str::limit($request->title, 30) }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($request->category) }}</div>
                                    @if($request->is_urgent)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Urgent</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $request->owner->name }}</div>
                            <div class="text-sm text-gray-500">{{ $request->owner->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">₦{{ number_format($request->amount_needed) }}
                            </div>
                            <div class="text-sm text-gray-500">Raised: ₦{{ number_format($request->amount_received) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $percentage = $request->amount_needed > 0 ? ($request->amount_received /
                            $request->amount_needed) * 100 : 0;
                            @endphp
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-600 h-2 rounded-full"
                                        style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($percentage, 1)
                                    }}%</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ $request->donations->count() }} donations</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($request->status === 'active') bg-green-100 text-green-800
                                @elseif($request->status === 'paused') bg-yellow-100 text-yellow-800
                                @elseif($request->status === 'completed') bg-blue-100 text-blue-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $request->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.donation-requests.show', $request->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No donation requests found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($donationRequests->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $donationRequests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection