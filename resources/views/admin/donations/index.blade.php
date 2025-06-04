@extends('admin.layouts.layout')

@section('title', 'Donations')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Donations Management</h1>
        <a href="{{ route('admin.donations.analytics') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
            </svg>
            <span>View Analytics</span>
        </a>
    </div>
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
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
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($pendingDonations) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Failed</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($failedDonations) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="successful" {{ request('status')==='successful' ? 'selected' : '' }}>Successful
                    </option>
                    <option value="failed" {{ request('status')==='failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                <select name="payment_status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Payment Status</option>
                    <option value="pending" {{ request('payment_status')==='pending' ? 'selected' : '' }}>Pending
                    </option>
                    <option value="success" {{ request('payment_status')==='success' ? 'selected' : '' }}>Success
                    </option>
                    <option value="failed" {{ request('payment_status')==='failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by donor name or email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-5 flex space-x-2">
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Filter</button>
                <a href="{{ route('admin.donations.index') }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Clear</a>
            </div>
        </form>
    </div>

    <!-- Donations Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($donations as $donation)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($donation->is_anonymous)
                            <span class="text-gray-500 italic">Anonymous</span>
                            @else
                            <div class="text-sm font-medium text-gray-900">{{ $donation->user->name ?? 'Unknown' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $donation->user->email ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $donation->donationRequest->title ??
                                'Deleted Campaign' }}</div>
                            <div class="text-sm text-gray-500">by {{ $donation->donationRequest->owner->name ??
                                'Unknown' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₦{{
                            number_format($donation->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($donation->status === 'successful') bg-green-100 text-green-800
                                @elseif($donation->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($donation->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($donation->payment_status === 'success') bg-green-100 text-green-800
                                @elseif($donation->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($donation->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                            $donation->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.donations.show', $donation->id) }}"
                                class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No donations found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($donations->hasPages())
        <div class="px-6 py-3 border-t">
            {{ $donations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection