@extends('admin.layouts.layout')

@section('title', 'Follow-up Requests')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Follow-up Requests</h1>
        <a href="{{ route('admin.consultations.index') }}"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            Back to Consultations
        </a>
    </div>

    <!-- Follow-up Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Follow-up Requests ({{ $followUpRequests->total() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested On</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($followUpRequests as $consultation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $consultation->user->name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $consultation->user->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($consultation->doctor)
                            <div class="text-sm font-medium text-gray-900">{{ $consultation->doctor->name }}</div>
                            @else
                            <span class="text-sm text-gray-500">AI Handled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $consultation->followUpRequestedBy->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $consultation->follow_up_requested_at ? $consultation->follow_up_requested_at->format('M
                            j, Y H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                {{ $consultation->follow_up_reason ?? 'No reason provided' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.consultations.show', $consultation->id) }}"
                                class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No follow-up requests found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($followUpRequests->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $followUpRequests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection