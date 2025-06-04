@extends('admin.layouts.layout')

@section('title', 'Event Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $event->title }}</h2>
            <p class="text-gray-600">Event ID: {{ $event->_id }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.events.edit', $event) }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Edit Event</a>
            <a href="{{ route('admin.events.participants', $event) }}"
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">View Participants</a>
            <a href="{{ route('admin.events.index') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Back to List</a>
        </div>
    </div>

    <!-- Event Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-indigo-600">{{ $eventStats['participants']['total'] }}</div>
            <div class="text-sm text-gray-600">Total Participants</div>
            <div class="text-xs text-gray-500 mt-1">{{ $eventStats['participants']['confirmed'] }} confirmed</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600">{{ $eventStats['participants']['attendance_rate'] }}%</div>
            <div class="text-sm text-gray-600">Attendance Rate</div>
            <div class="text-xs text-gray-500 mt-1">{{ $eventStats['participants']['pending'] }} pending</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600">{{ $eventStats['reactions']['likes'] }}</div>
            <div class="text-sm text-gray-600">Likes</div>
            <div class="text-xs text-gray-500 mt-1">{{ $eventStats['reactions']['dislikes'] }} dislikes</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-purple-600">
                @if($eventStats['capacity_utilization'])
                {{ $eventStats['capacity_utilization'] }}%
                @else
                N/A
                @endif
            </div>
            <div class="text-sm text-gray-600">Capacity Utilization</div>
            <div class="text-xs text-gray-500 mt-1">
                @if($event->capacity)
                {{ $event->capacity }} max capacity
                @else
                No limit set
                @endif
            </div>
        </div>
    </div>

    <!-- Event Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Information</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($event->status == 'active') bg-green-100 text-green-800
                            @elseif($event->status == 'draft') bg-yellow-100 text-yellow-800
                            @elseif($event->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($event->status) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $event->category }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $event->location }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Event Manager</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $event->eventManager->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Start Time</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $event->start_time->format('M j, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">End Time</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $event->end_time->format('M j, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Requires Verification</dt>
                    <dd class="mt-1 text-sm">
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $event->requires_verification ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $event->requires_verification ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Provides Certificate</dt>
                    <dd class="mt-1 text-sm">
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $event->provides_certificate ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $event->provides_certificate ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
            <p class="text-sm text-gray-700 mb-4">{{ $event->description }}</p>

            @if($event->contact_info)
            <div class="mt-4">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Contact Information</h4>
                <p class="text-sm text-gray-700">{{ $event->contact_info }}</p>
            </div>
            @endif

            @if($event->banner_url)
            <div class="mt-4">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Event Banner</h4>
                <img src="{{ $event->banner_url }}" alt="Event Banner" class="w-full h-32 object-cover rounded-md">
            </div>
            @endif
        </div>
    </div>

    <!-- Status Update -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Status Update</h3>
        <form method="POST" action="{{ route('admin.events.update-status', $event) }}"
            class="flex items-center space-x-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="border-gray-300 rounded-md">
                    <option value="draft" {{ $event->status == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ $event->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="cancelled" {{ $event->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ $event->status == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="pt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Update Status
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Participants -->
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Participants</h3>
            <a href="{{ route('admin.events.participants', $event) }}"
                class="text-indigo-600 hover:text-indigo-900 text-sm">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($participants->take(5) as $participant)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $participant->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $participant->user->email ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($participant->status == 'confirmed') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($participant->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $participant->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST"
                                action="{{ route('admin.events.remove-participant', [$event, $participant]) }}"
                                class="inline" onsubmit="return confirm('Remove this participant?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No participants yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Reactions -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Reactions</h3>
        <div class="space-y-3">
            @forelse($reactions as $reaction)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div class="flex items-center space-x-3">
                    <div class="text-lg">
                        @if($reaction->reaction_type == 'like')
                        👍
                        @else
                        👎
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $reaction->user->name ?? 'Anonymous' }}</div>
                        @if($reaction->comment)
                        <div class="text-sm text-gray-600">{{ Str::limit($reaction->comment, 60) }}</div>
                        @endif
                    </div>
                </div>
                <div class="text-xs text-gray-500">{{ $reaction->created_at->diffForHumans() }}</div>
            </div>
            @empty
            <p class="text-gray-500 text-sm">No reactions yet</p>
            @endforelse
        </div>
    </div>

    <!-- Danger Zone -->
    @if(!$event->eventParticipants()->exists())
    <div class="bg-red-50 p-6 rounded-lg border border-red-200">
        <h3 class="text-lg font-semibold text-red-900 mb-2">Danger Zone</h3>
        <p class="text-sm text-red-700 mb-4">This action cannot be undone. This will permanently delete the event.</p>
        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
            onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Delete Event
            </button>
        </form>
    </div>
    @endif
</div>
@endsection