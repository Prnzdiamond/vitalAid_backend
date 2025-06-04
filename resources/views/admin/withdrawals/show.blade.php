@extends('admin.layouts.layout')

@section('title', 'Withdrawal Request Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Withdrawal Request #{{ substr($withdrawal->_id, -8) }}</h1>
            <p class="text-gray-600">Submitted {{ $withdrawal->created_at->format('M d, Y \a\t H:i') }}</p>
        </div>
        <a href="{{ route('admin.withdrawals.index') }}"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            Back to List
        </a>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center space-x-4">
        <span class="px-3 py-1 text-sm font-semibold rounded-full
            {{ $withdrawal->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
            {{ $withdrawal->status == 'approved' ? 'bg-green-100 text-green-800' : '' }}
            {{ $withdrawal->status == 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
            {{ ucfirst($withdrawal->status) }}
        </span>
        @if($withdrawal->status == 'approved')
        <span class="text-sm text-gray-600">Approved {{ $withdrawal->approved_at->format('M d, Y \a\t H:i') }}</span>
        @elseif($withdrawal->status == 'rejected')
        <span class="text-sm text-gray-600">Rejected {{ $withdrawal->rejected_at->format('M d, Y \a\t H:i') }}</span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Withdrawal Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Withdrawal Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Amount Requested</dt>
                        <dd class="text-lg font-semibold text-gray-900">₦{{ number_format($withdrawal->amount, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Available Amount</dt>
                        <dd
                            class="text-lg font-semibold {{ $availableAmount >= $withdrawal->amount ? 'text-green-600' : 'text-red-600' }}">
                            ₦{{ number_format($availableAmount, 2) }}
                        </dd>
                    </div>
                </dl>
                @if($withdrawal->payout_reference)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Payout Reference</dt>
                    <dd class="text-sm text-gray-900">{{ $withdrawal->payout_reference }}</dd>
                </div>
                @endif
            </div>

            <!-- Organization Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Organization Details</h3>
                <div class="flex items-start space-x-4">
                    @if($withdrawal->organization->logo)
                    <img src="{{ asset('storage/' . $withdrawal->organization->logo) }}" alt="Logo"
                        class="w-16 h-16 rounded-full object-cover">
                    @else
                    <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 font-medium">{{ substr($withdrawal->organization->name, 0, 2)
                            }}</span>
                    </div>
                    @endif
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900">{{ $withdrawal->organization->name }}</h4>
                        <p class="text-gray-600">{{ $withdrawal->organization->email }}</p>
                        @if($withdrawal->organization->phone_number)
                        <p class="text-gray-600">{{ $withdrawal->organization->phone_number }}</p>
                        @endif
                        @if($withdrawal->organization->location)
                        <p class="text-gray-600">{{ $withdrawal->organization->location }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            @if($withdrawal->bank_details)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bank Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($withdrawal->bank_details as $key => $value)
                    @if($value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="text-sm text-gray-900">{{ $value }}</dd>
                    </div>
                    @endif
                    @endforeach
                </dl>
            </div>
            @endif

            <!-- Donation Request Details -->
            @if($withdrawal->donationRequest)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Related Donation Request</h3>
                <div class="flex items-start space-x-4">
                    @if($withdrawal->donationRequest->banner_url)
                    <img src="{{ asset('storage/' . $withdrawal->donationRequest->banner_url) }}" alt="Banner"
                        class="w-20 h-20 rounded-lg object-cover">
                    @endif
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900">{{ $withdrawal->donationRequest->title }}</h4>
                        <p class="text-gray-600 mt-2">{{ Str::limit($withdrawal->donationRequest->description, 200) }}
                        </p>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount Needed</dt>
                                <dd class="text-sm text-gray-900">₦{{
                                    number_format($withdrawal->donationRequest->amount_needed, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount Received</dt>
                                <dd class="text-sm text-gray-900">₦{{
                                    number_format($withdrawal->donationRequest->amount_received, 2) }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Notes -->
            @if($withdrawal->admin_notes || $withdrawal->rejection_reason)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Notes</h3>
                @if($withdrawal->admin_notes)
                <div class="mb-4">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $withdrawal->admin_notes }}</dd>
                </div>
                @endif
                @if($withdrawal->rejection_reason)
                <div>
                    <dt class="text-sm font-medium text-red-500">Rejection Reason</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $withdrawal->rejection_reason }}</dd>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            @if($withdrawal->status == 'pending')
            <!-- Approve Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-green-700 mb-4">Approve Withdrawal</h3>
                <form action="{{ route('admin.withdrawals.approve', $withdrawal->_id) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payout Reference</label>
                            <input type="text" name="payout_reference"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Admin Notes</label>
                            <textarea name="admin_notes" rows="3"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 font-medium">
                            Approve Withdrawal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reject Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-red-700 mb-4">Reject Withdrawal</h3>
                <form action="{{ route('admin.withdrawals.reject', $withdrawal->_id) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rejection Reason *</label>
                            <textarea name="rejection_reason" rows="4" required
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 font-medium">
                            Reject Withdrawal
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Summary Card -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Status</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ ucfirst($withdrawal->status) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Requested Amount</dt>
                        <dd class="text-sm font-medium text-gray-900">₦{{ number_format($withdrawal->amount, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Available Amount</dt>
                        <dd
                            class="text-sm font-medium {{ $availableAmount >= $withdrawal->amount ? 'text-green-600' : 'text-red-600' }}">
                            ₦{{ number_format($availableAmount, 2) }}
                        </dd>
                    </div>
                    @if($availableAmount < $withdrawal->amount)
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm text-red-700">⚠️ Insufficient funds available</p>
                        </div>
                        @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection