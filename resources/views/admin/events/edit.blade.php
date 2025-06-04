@extends('admin.layouts.layout')

@section('title', 'Edit Event')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Edit Event</h2>
        <a href="{{ route('admin.events.show', $event) }}"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Back to Event</a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="POST" action="{{ route('admin.events.update', $event) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}"
                        class="w-full border-gray-300 rounded-md" required>
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="4" class="w-full border-gray-300 rounded-md"
                        required>{{ old('description', $event->description) }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                    <input type="text" name="location" value="{{ old('location', $event->location) }}"
                        class="w-full border-gray-300 rounded-md" required>
                    @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Event Manager -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Manager *</label>
                    <select name="event_manager" class="w-full border-gray-300 rounded-md" required>
                        <option value="">Select Manager</option>
                        @foreach($eventManagers as $manager)
                        <option value="{{ $manager->_id }}" {{ old('event_manager', $event->event_manager) ==
                            $manager->_id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('event_manager')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                    <input type="datetime-local" name="start_time"
                        value="{{ old('start_time', $event->start_time->format('Y-m-d\TH:i')) }}"
                        class="w-full border-gray-300 rounded-md" required>
                    @error('start_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                    <input type="datetime-local" name="end_time"
                        value="{{ old('end_time', $event->end_time->format('Y-m-d\TH:i')) }}"
                        class="w-full border-gray-300 rounded-md" required>
                    @error('end_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" class="w-full border-gray-300 rounded-md" required>
                        <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft
                        </option>
                        <option value="active" {{ old('status', $event->status) == 'active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : ''
                            }}>Cancelled</option>
                        <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : ''
                            }}>Completed</option>
                    </select>
                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <input type="text" name="category" value="{{ old('category', $event->category) }}"
                        class="w-full border-gray-300 rounded-md" required>
                    @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}"
                        class="w-full border-gray-300 rounded-md" min="1">
                    @error('capacity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Contact Info -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Information</label>
                    <textarea name="contact_info" rows="2"
                        class="w-full border-gray-300 rounded-md">{{ old('contact_info', $event->contact_info) }}</textarea>
                    @error('contact_info')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Banner URL -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner URL</label>
                    <input type="url" name="banner_url" value="{{ old('banner_url', $event->banner_url) }}"
                        class="w-full border-gray-300 rounded-md">
                    @error('banner_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Checkboxes -->
                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="requires_verification" value="1" {{ old('requires_verification',
                            $event->requires_verification) ? 'checked' : '' }}
                        class="rounded border-gray-300">
                        <span class="ml-2 text-sm text-gray-700">Requires Verification</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="provides_certificate" value="1" {{ old('provides_certificate',
                            $event->provides_certificate) ? 'checked' : '' }}
                        class="rounded border-gray-300">
                        <span class="ml-2 text-sm text-gray-700">Provides Certificate</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.events.show', $event) }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update
                    Event</button>
            </div>
        </form>
    </div>
</div>
@endsection