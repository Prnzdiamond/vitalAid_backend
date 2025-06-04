@extends('admin.layouts.layout')

@section('title', 'Consultation Analytics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Consultation Analytics</h1>
        <a href="{{ route('admin.consultations.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Back to
            Consultations</a>
    </div>

    <!-- Status Breakdown Cards -->
    <div class="grid grid-cols-1 md:grid-cols-{{ $statusBreakdown->count() }} gap-4">
        @foreach($statusBreakdown as $status)
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold
                @if($status->status === 'completed') text-green-600
                @elseif($status->status === 'in_progress') text-blue-600
                @else text-yellow-600
                @endif">
                {{ number_format($status->count) }}
            </div>
            <div class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $status->status)) }}</div>
        </div>
        @endforeach
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Consultations Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Consultations ({{ now()->year }})</h2>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Distribution</h2>
            <div class="h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Performing Doctors -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Top Performing Doctors</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consultations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topDoctors as $doctor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div
                                    class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                    {{ substr($doctor->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $doctor->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $doctor->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($doctor->consultations_handled_count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900">{{
                                    number_format($doctor->average_rating, 1) }}</span>
                                <div class="ml-2 flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++) @if($i <=$doctor->average_rating)
                                        ★
                                        @else
                                        ☆
                                        @endif
                                        @endfor
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $doctor->is_verified ? 'Verified' : 'Unverified' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No doctors found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Completed Consultations Summary -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $completedConsultations->count() }}</div>
                <div class="text-sm text-gray-600">Recent Completed</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{
                    number_format($completedConsultations->whereNotNull('rating')->avg('rating') ?? 0, 1) }}</div>
                <div class="text-sm text-gray-600">Avg Rating</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $completedConsultations->where('follow_up_requested',
                    true)->count() }}</div>
                <div class="text-sm text-gray-600">Follow-up Requests</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Monthly Consultations Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Consultations',
            data: [
                @foreach(range(1, 12) as $month)
                    {{ $monthlyConsultations->where('month', $month)->first()->count ?? 0 }},
                @endforeach
            ],
            borderColor: 'rgb(79, 70, 229)',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($statusBreakdown as $status)
                '{{ ucfirst(str_replace("_", " ", $status->status)) }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($statusBreakdown as $status)
                    {{ $status->count }},
                @endforeach
            ],
            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
@endsection