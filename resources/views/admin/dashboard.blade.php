@extends('admin.layouts.layout')

@section('title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg text-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Welcome back, {{ auth('admin')->user()->name }}!</h2>
                <p class="text-indigo-100 mt-1">Here's what's happening with VitalAid today.</p>
            </div>
            <div class="hidden md:block">
                <svg class="w-16 h-16 text-indigo-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Users Stats -->
        <div class="admin-card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['users']['total'])
                                }}</div>
                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                {{ $stats['users']['verification_rate'] }}% verified
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Verified: {{ number_format($stats['users']['verified']) }}</span>
                    <span>Pending: {{ number_format($stats['users']['pending_verification']) }}</span>
                </div>
            </div>
        </div>

        <!-- Events Stats -->
        <div class="admin-card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Events</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">{{
                                number_format($stats['events']['total']) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Upcoming: {{ number_format($stats['events']['upcoming']) }}</span>
                    <span>Active: {{ number_format($stats['events']['active']) }}</span>
                </div>
            </div>
        </div>

        <!-- Donations Stats -->
        <div class="admin-card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Raised</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">₦{{
                                number_format($stats['donations']['total_amount']) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Donations: {{ number_format($stats['donations']['total_count']) }}</span>
                    <span>Active Requests: {{ number_format($stats['donations']['active_requests']) }}</span>
                </div>
            </div>
        </div>

        <!-- Consultations Stats -->
        <div class="admin-card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Consultations</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">{{
                                number_format($stats['consultations']['total']) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Active: {{ number_format($stats['consultations']['active']) }}</span>
                    <span>Completed: {{ number_format($stats['consultations']['completed']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Communities Stats -->
        <div class="admin-card bg-white rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Communities</h3>
                    <div class="mt-2">
                        <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['communities']['total'])
                            }}</div>
                        <p class="text-sm text-gray-600">{{ number_format($stats['communities']['members']) }} total
                            members</p>
                    </div>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-card bg-white rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ {{ route('admin.verifications.index') }} }}"
                    class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Verifications
                </a>
                <a href="{{ {{ route('admin.users.index') }} }}"
                    class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                    </svg>
                    Manage Users
                </a>
                <a href="{{ {{ route('admin.events.index') }}"
                    class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                        </path>
                    </svg>
                    View Events
                </a>
                <a href="{{ route('admin.reports.index') }}"
                    class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Generate Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Activity Chart -->
        <div class="admin-card bg-white rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Monthly Activity</h3>
                <div class="loading-spinner hidden" id="chart-loading"></div>
            </div>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- User Role Distribution -->
        <div class="admin-card bg-white rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">User Distribution</h3>
            <div class="h-64">
                <canvas id="userRoleChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="admin-card bg-white rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivities['users'] as $user)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 1)
                                        }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="status-{{ $user->role === 'user' ? 'active' : 'approved' }}">{{
                                ucfirst($user->role) }}</span>
                            @if($user->is_verified)
                            <span class="status-approved">Verified</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recent users found.
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View
                    all users →</a>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="admin-card bg-white rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Events</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivities['events'] as $event)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                            <p class="text-sm text-gray-500">{{ $event->location }}</p>
                            <p class="text-xs text-gray-400">{{ $event->start_time->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <span class="status-{{ $event->status === 'active' ? 'active' : 'pending' }}">{{
                                ucfirst($event->status) }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recent events found.
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('admin.events.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View
                    all events →</a>
            </div>
        </div>
    </div>

    <!-- Recent Donations & Consultations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Donations -->
        <div class="admin-card bg-white rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Donations</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivities['donations'] as $donation)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">₦{{ number_format($donation->amount) }}</p>
                            <p class="text-sm text-gray-500">{{ $donation->user->name ?? 'Anonymous' }}</p>
                            <p class="text-xs text-gray-400">{{ $donation->donationRequest->title ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span
                                class="status-{{ $donation->payment_status === 'successful' ? 'approved' : 'pending' }}">
                                {{ ucfirst($donation->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recent donations found.
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('admin.donations.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-500">View all donations →</a>
            </div>
        </div>

        <!-- Recent Consultations -->
        <div class="admin-card bg-white rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Consultations</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivities['consultations'] as $consultation)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $consultation->user->name ?? 'User' }}</p>
                            <p class="text-sm text-gray-500">
                                @if($consultation->doctor)
                                with {{ $consultation->doctor->name }}
                                @else
                                AI Consultation
                                @endif
                            </p>
                            <p class="text-xs text-gray-400">{{ $consultation->last_message_at?->format('M d, Y H:i') ??
                                $consultation->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <span
                                class="status-{{ $consultation->status === 'completed' ? 'approved' : ($consultation->status === 'in_progress' ? 'active' : 'pending') }}">
                                {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recent consultations found.
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('admin.consultations.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-500">View all consultations →</a>
            </div>
        </div>
    </div>

    @if($stats['users']['pending_verification'] > 0)
    <!-- Pending Verifications Alert -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>{{ $stats['users']['pending_verification'] }} verification{{
                        $stats['users']['pending_verification'] > 1 ? 's' : '' }} pending review.</strong>
                    <a href="{{ route('admin.verifications.index') }}"
                        class="font-medium underline text-yellow-700 hover:text-yellow-600">
                        Review now →
                    </a>
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Monthly Activity Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const chartLoading = document.getElementById('chart-loading');

    chartLoading.classList.remove('hidden');

    // Fetch monthly stats
    fetch('{{ route("admin.monthly-stats") }}')
        .then(response => response.json())
        .then(data => {
            chartLoading.classList.add('hidden');

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.month),
                    datasets: [
                        {
                            label: 'Users',
                            data: data.map(item => item.users),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Events',
                            data: data.map(item => item.events),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Donations (₦)',
                            data: data.map(item => item.donations),
                            borderColor: 'rgb(251, 191, 36)',
                            backgroundColor: 'rgba(251, 191, 36, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Count'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Amount (₦)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching monthly stats:', error);
            chartLoading.classList.add('hidden');
        });

    // User Role Distribution Chart
    const userRoleCtx = document.getElementById('userRoleChart').getContext('2d');

    new Chart(userRoleCtx, {
        type: 'doughnut',
        data: {
            labels: ['Regular Users', 'Health Experts', 'Charities', 'Communities'],
            datasets: [{
                data: [
                    {{ $stats['users']['total'] - $stats['communities']['total'] }}, // Approximate regular users
                    {{ App\Models\User::where('role', 'health_expert')->count() }},
                    {{ App\Models\User::where('role', 'charity')->count() }},
                    {{ $stats['communities']['total'] }}
                ],
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(251, 191, 36)',
                    'rgb(147, 51, 234)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
@endsection