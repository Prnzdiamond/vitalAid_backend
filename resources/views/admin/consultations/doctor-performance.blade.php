@extends('admin.layouts.layout')

@section('title', 'Doctor Performance')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Doctor Performance</h1>
        <a href="{{ route('admin.consultations.index') }}"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            Back to Consultations
        </a>
    </div>

    <!-- Performance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Health Experts Performance ({{ $doctors->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Specialization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Handled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Follow-ups</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Average Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($doctors as $doctor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $doctor->name }}</div>
                            <div class="text-sm text-gray-500">{{ $doctor->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $doctor->specialization ?? 'General' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($doctor->consultations_handled_count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($doctor->completed_consultations) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($doctor->follow_up_requests) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($doctor->average_rating > 0)
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900">{{ number_format($doctor->average_rating, 1)
                                    }}/5</span>
                                <div class="flex ml-1">
                                    @for($i = 1; $i <= 5; $i++) <svg
                                        class="h-4 w-4 {{ $i <= $doctor->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        @endfor
                                </div>
                            </div>
                            @else
                            <span class="text-sm text-gray-400">No ratings</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $doctor->is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $doctor->is_verified ? 'Verified' : 'Unverified' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No doctors found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection