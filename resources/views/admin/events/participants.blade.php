@extends('admin.layouts.layout')

@section('title', 'Event Participants')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Event Participants</h2>
            <p class="text-gray-600">{{ $event->title }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.events.show', $event) }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">View Event</a>
            <a href="{{ route('admin.events.index') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Back to Events</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-md">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Confirmed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Participants</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full border-gray-300 rounded-md" placeholder="Name or email">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Participants Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Participants ({{ $participants->total() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Participant
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Joined Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($participants as $participant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ substr($participant->user->name ?? 'N/A', 0, 2) }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $participant->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: {{ $participant->user->_id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $participant->user->email ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($participant->status == 'confirmed') bg-green-100 text-green-800
                                @elseif($participant->status == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($participant->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $participant->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <form method="POST"
                                action="{{ route('admin.events.remove-participant', [$event, $participant]) }}"
                                class="inline"
                                onsubmit="return confirm('Are you sure you want to remove this participant?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                    </path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No participants</h3>
                                <p class="mt-1 text-sm text-gray-500">No participants found matching your criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center">
        <div class="text-sm text-gray-700">
            Showing {{ $participants->firstItem() ?? 0 }} to {{ $participants->lastItem() ?? 0 }}
            of {{ $participants->total() }} participants
        </div>
        {{ $participants->appends(request()->query())->links() }}
    </div>
</div>
@endsection