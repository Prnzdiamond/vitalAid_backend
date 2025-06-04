@extends('admin.layouts.layout')

@section('title', 'Donation Request Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('admin.donation-requests.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr;
                Back to Donation Requests</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $donationRequest->title }}</h1>
        </div>
        <div class="flex space-x-3">
            <form method="POST" action="{{ route('admin.donation-requests.update-status', $donationRequest->id) }}"
                class="inline">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="active" {{ $donationRequest->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ $donationRequest->status === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ $donationRequest->status === 'completed' ? 'selected' : '' }}>Completed
                    </option>
                    <option value="cancelled" {{ $donationRequest->status === 'cancelled' ? 'selected' : '' }}>Cancelled
                    </option>
                </select>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600">₦{{ number_format($totalAmountRaised) }}</div>
            <div class="text-sm text-gray-600">Total Raised</div>
            <div class="text-xs text-gray-500 mt-1">of ₦{{ number_format($donationRequest->amount_needed) }} needed
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($totalDonations) }}</div>
            <div class="text-sm text-gray-600">Total Donations</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-purple-600">₦{{ number_format($averageDonation) }}</div>
            <div class="text-sm text-gray-600">Average Donation</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-indigo-600">{{ number_format($progressPercentage, 1) }}%</div>
            <div class="text-sm text-gray-600">Progress</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Request Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Request Information</h2>

                @if($donationRequest->banner_url)
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $donationRequest->banner_url) }}" alt="Banner"
                        class="w-full h-48 object-cover rounded-lg">
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst($donationRequest->category) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                            @if($donationRequest->status === 'active') bg-green-100 text-green-800
                            @elseif($donationRequest->status === 'paused') bg-yellow-100 text-yellow-800
                            @elseif($donationRequest->status === 'completed') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($donationRequest->status) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Urgency</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1 {{ $donationRequest->is_urgent ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $donationRequest->is_urgent ? 'Urgent' : 'Normal' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created At</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->updated_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $donationRequest->description }}
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-6">
                    <div class="flex justify-between text-sm font-medium text-gray-900 mb-2">
                        <span>Fundraising Progress</span>
                        <span>{{ number_format($progressPercentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-600 h-3 rounded-full transition-all duration-300"
                            style="width: {{ min($progressPercentage, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>₦{{ number_format($totalAmountRaised) }} raised</span>
                        <span>₦{{ number_format($donationRequest->amount_needed) }} goal</span>
                    </div>
                </div>

                @if($donationRequest->other_images && count($donationRequest->other_images) > 0)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($donationRequest->other_images as $image)
                        <img src="{{ asset('storage/' . $image) }}" alt="Additional image"
                            class="w-full h-24 object-cover rounded-lg">
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Recent Donations -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Donations</h2>
                @if($recentDonations->count() > 0)
                <div class="space-y-4">
                    @foreach($recentDonations as $donation)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-medium">
                                @if($donation->is_anonymous || !$donation->user)
                                A
                                @else
                                {{ substr($donation->user->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($donation->is_anonymous || !$donation->user)
                                    Anonymous Donor
                                    @else
                                    {{ $donation->user->name }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">{{ $donation->created_at->format('M d, Y H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">₦{{ number_format($donation->amount) }}</div>
                            <div class="text-sm text-gray-500">{{ ucfirst($donation->payment_status) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No donations yet</p>
                @endif
            </div>
        </div>

        <!-- Organization Details -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Organization Details</h2>

                <div class="text-center mb-4">
                    @if($donationRequest->owner->logo)
                    <img src="{{ asset('storage/' . $donationRequest->owner->logo) }}" alt="Organization logo"
                        class="h-16 w-16 rounded-full mx-auto object-cover">
                    @else
                    <div
                        class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xl mx-auto">
                        {{ substr($donationRequest->owner->name, 0, 1) }}
                    </div>
                    @endif
                    <h3 class="mt-2 text-lg font-medium text-gray-900">{{ $donationRequest->owner->name }}</h3>
                    <p class="text-sm text-gray-500">{{ ucfirst($donationRequest->owner->role) }}</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->owner->email }}</p>
                    </div>
                    @if($donationRequest->owner->phone_number)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->owner->phone_number }}</p>
                    </div>
                    @endif
                    @if($donationRequest->owner->location)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->owner->location }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Verification Status</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1 {{ $donationRequest->owner->is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $donationRequest->owner->is_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Member Since</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $donationRequest->owner->created_at->format('M Y') }}
                        </p>
                    </div>
                </div>

                @if($donationRequest->owner->description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700">About</label>
                    <p class="mt-1 text-sm text-gray-900">{{ Str::limit($donationRequest->owner->description, 200) }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.users.show', $donationRequest->owner->id) }}"
                        class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        View Organization Profile
                    </a>
                    <a href="{{ route('admin.donations.index', ['donation_request_id' => $donationRequest->id]) }}"
                        class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        View All Donations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection