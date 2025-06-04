@extends('admin.layouts.layout')

@section('title', 'Events Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Events Management</h2>
        <div class="flex space-x-3">
            <button onclick="document.getElementById('bulk-actions').classList.toggle('hidden')"
                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Bulk Actions
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-md">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                    <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <input type="text" name="category" value="{{ request('category') }}"
                    class="w-full border-gray-300 rounded-md" placeholder="Enter category">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="flex">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="flex-1 border-gray-300 rounded-l-md" placeholder="Title or description">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700">
                        Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions (Hidden by default) -->
    <div id="bulk-actions" class="hidden bg-yellow-50 p-4 rounded-lg border border-yellow-200">
        <form method="POST" action="{{ route('admin.events.bulk-action') }}" onsubmit="return confirm('Are you sure?')">
            @csrf
            <div class="flex items-center space-x-4">
                <select name="action" class="border-gray-300 rounded-md" required>
                    <option value="">Select Action</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="cancel">Cancel</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700">
                    Apply to Selected
                </button>
                <span class="text-sm text-gray-600">Select events below to apply bulk actions</span>
            </div>
        </form>
    </div>

    <!-- Events Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" onchange="toggleAll(this)">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?{{ http_build_query(array_merge(request()->all(), ['sort' => 'title', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                Title
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?{{ http_build_query(array_merge(request()->all(), ['sort' => 'start_time', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                Start Time
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Participants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="event_ids[]" value="{{ $event->_id }}" form="bulk-actions">
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ Str::limit($event->title, 30) }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($event->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $event->eventManager->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($event->status == 'active') bg-green-100 text-green-800
                                @elseif($event->status == 'draft') bg-yellow-100 text-yellow-800
                                @elseif($event->status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->category }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $event->start_time->format('M j, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $event->eventParticipants->count() }}
                            @if($event->capacity)
                            / {{ $event->capacity }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                            <a href="{{ route('admin.events.show', $event) }}"
                                class="text-indigo-600 hover:text-indigo-900">View</a>
                            <a href="{{ route('admin.events.edit', $event) }}"
                                class="text-blue-600 hover:text-blue-900">Edit</a>
                            <form method="POST" action="{{ route('admin.events.update-status', $event) }}"
                                class="inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                    class="text-xs border-gray-300 rounded">
                                    <option value="draft" {{ $event->status == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="active" {{ $event->status == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="cancelled" {{ $event->status == 'cancelled' ? 'selected' : ''
                                        }}>Cancelled</option>
                                    <option value="completed" {{ $event->status == 'completed' ? 'selected' : ''
                                        }}>Completed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No events found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center">
        <div class="text-sm text-gray-700">
            Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{ $events->total() }} results
        </div>
        {{ $events->appends(request()->query())->links() }}
    </div>
</div>

<script>
    function toggleAll(source) {
    const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
}
</script>
@endsection