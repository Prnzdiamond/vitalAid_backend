@extends('admin.layouts.layout')

@section('title', 'Donation Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Donation Details</h1>
        <a href="{{ route('admin.donations.index') }}"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
            ← Back to Donations
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Donation Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Donation Information</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">₦{{ number_format($donation->amount, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $donation->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($donation->status === 'successful') bg-green-100 text-green-800
                                @elseif($donation->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($donation->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($donation->payment_status === 'success') bg-green-100 text-green-800
                                @elseif($donation->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($donation->payment_status) }}
                            </span>
                        </dd>
                    </div>
                    @if($donation->paystack_reference)
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Payment Reference</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $donation->paystack_reference }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Campaign Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Campaign Information</h2>
                @if($donation->donationRequest)
                <div class="space-y-4">
                    <div>
                        <h3 class="text-base font-medium text-gray-900">{{ $donation->donationRequest->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($donation->donationRequest->description,
                            200) }}</p>
                    </div>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Target Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">₦{{
                                number_format($donation->donationRequest->amount_needed, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount Raised</dt>
                            <dd class="mt-1 text-sm text-gray-900">₦{{
                                number_format($donation->donationRequest->amount_received ?? 0, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Campaign Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($donation->donationRequest->status === 'active') bg-green-100 text-green-800
                                    @elseif($donation->donationRequest->status === 'completed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($donation->donationRequest->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                    @if($donation->donationRequest->owner)
                    <div class="border-t pt-4">
                        <dt class="text-sm font-medium text-gray-500">Campaign Owner</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $donation->donationRequest->owner->name }}</dd>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-gray-500 italic">Campaign information not available (may have been deleted)</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Donor Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Donor Information</h2>
                @if($donation->is_anonymous)
                <div class="text-center py-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 italic">Anonymous Donor</p>
                </div>
                @elseif($donation->user)
                <div class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $donation->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $donation->user->email }}</dd>
                    </div>
                    @if($donation->user->phone_number)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $donation->user->phone_number }}</dd>
                    </div>
                    @endif
                    @if($donation->user->location)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $donation->user->location }}</dd>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-gray-500 italic">Donor information not available</p>
                @endif
            </div>

            <!-- Quick Stats -->
            @if($donation->user && !$donation->is_anonymous)
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Donor Statistics</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Donations</span>
                        <span class="text-sm font-medium text-gray-900">{{ $donation->user->donation()->count()
                            }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Donated</span>
                        <span class="text-sm font-medium text-gray-900">₦{{
                            number_format($donation->user->donation()->where('status', 'successful')->sum('amount'), 2)
                            }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Member Since</span>
                        <span class="text-sm font-medium text-gray-900">{{ $donation->user->created_at->format('M Y')
                            }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Actions</h2>
                <div class="space-y-2">
                    @if($donation->donationRequest)
                    <a href="#"
                        class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        View Campaign
                    </a>
                    @endif
                    @if($donation->user && !$donation->is_anonymous)
                    <a href="#"
                        class="block w-full bg-gray-100 text-gray-700 text-center px-4 py-2 rounded-md hover:bg-gray-200 text-sm">
                        View Donor Profile
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection