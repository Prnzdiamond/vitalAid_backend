@extends('admin.layouts.layout')

@section('title', 'Consultations')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-md bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Consultations</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalConsultations) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-md bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($activeConsultations) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-md bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($completedConsultations) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-md bg-purple-100">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Average Rating</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($averageRating, 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.consultations.analytics') }}"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">View Analytics</p>
                    <p class="text-sm text-gray-500">Charts and trends</p>
                </div>
            </a>

            <a href="{{ route('admin.consultations.doctor-performance') }}"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Doctor Performance</p>
                    <p class="text-sm text-gray-500">Ratings and statistics</p>
                </div>
            </a>

            <a href="{{ route('admin.consultations.follow-up-requests') }}"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Follow-up Requests</p>
                    <p class="text-sm text-gray-500">Pending follow-ups</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">All Status</option>
                    <option value="requested" {{ request('status')==='requested' ? 'selected' : '' }}>Requested</option>
                    <option value="in_progress" {{ request('status')==='in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="completed" {{ request('status')==='completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="User or doctor name..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
                <a href="{{ route('admin.consultations.index') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">Reset</a>
            </div>
        </form>
    </div>

    <!-- Consultations Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Consultations ({{ $consultations->total() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($consultations as $consultation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $consultation->user->name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $consultation->user->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($consultation->doctor)
                            <div class="text-sm font-medium text-gray-900">{{ $consultation->doctor->name }}</div>
                            <div class="text-sm text-gray-500">{{ $consultation->doctor->specialization ?? 'Health
                                Expert' }}</div>
                            @else
                            <span class="text-sm text-gray-500">AI Handled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' :
                                   ($consultation->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                            </span>
                            @if($consultation->follow_up_requested)
                            <span
                                class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Follow-up</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($consultation->rating)
                            <div class="flex items-center">
                                <span>{{ $consultation->rating }}/5</span>
                                <div class="flex ml-1">
                                    @for($i = 1; $i <= 5; $i++) <svg
                                        class="h-4 w-4 {{ $i <= $consultation->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        @endfor
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400">Not rated</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $consultation->last_message_at ? $consultation->last_message_at->format('M j, Y H:i') :
                            'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.consultations.show', $consultation->id) }}"
                                class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No consultations found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($consultations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $consultations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection