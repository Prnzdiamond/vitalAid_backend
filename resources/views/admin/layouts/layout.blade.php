<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - VitalAid Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    <!-- Additional Styles -->
    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-900 text-white fixed inset-y-0 left-0 transform transition-transform duration-200 ease-in-out z-30"
            id="sidebar">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-indigo-800">
                <h1 class="text-xl font-bold">VitalAid Admin</h1>
            </div>

            <!-- Navigation -->
            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Users -->
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Users
                    </a>

                    <!-- Verifications -->
                    <a href="{{ route('admin.verifications.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.verifications.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verifications
                        @if(isset($pendingVerifications) && $pendingVerifications > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">{{
                            $pendingVerifications }}</span>
                        @endif
                    </a>

                    <!-- Events -->
                    <a href="{{ route('admin.events.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.events.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z">
                            </path>
                        </svg>
                        Events
                    </a>

                    <!-- Donations Requests -->
                    <a href="{{ route('admin.donation-requests.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.donation-requests.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                        Donations Requests
                    </a>

                    <!-- Donations -->
                    <a href="{{ route('admin.donations.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.donations.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                        Donations
                    </a>

                    <!-- Consultations -->
                    <a href="{{ route('admin.consultations.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.consultations.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        Consultations
                    </a>

                    <!-- Communities -->
                    <a href="{{ route('admin.communities.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.communities.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        Communities
                    </a>

                    <!-- Withdrawals -->
                    <a href="{{ route('admin.withdrawals.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.withdrawals.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                        Withdrawals
                    </a>

                    <!-- Reports -->
                    <a href="{{ route('admin.reports.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-700' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        Reports
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-4">
                        <!-- Mobile menu button -->
                        <button class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
                            id="mobile-menu-button">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <div class="flex-1">
                            <h1 class="text-2xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                        </div>

                        <!-- User Menu -->
                        <div class="relative">
                            <div class="flex items-center space-x-4">
                                <!-- Notifications -->
                                <button class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-md">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                        </path>
                                    </svg>
                                </button>

                                <!-- User dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <div
                                            class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                            {{ substr(auth('admin')->user()->name, 0, 1) }}
                                        </div>
                                        <span class="ml-2 text-gray-700 font-medium">{{ auth('admin')->user()->name
                                            }}</span>
                                        <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false" x-transition
                                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                        <a href="#"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                        <a href="#"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                        <hr class="my-1">
                                        <form method="POST" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Sign out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                    @endif

                    <!-- Page Content -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile sidebar overlay -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20 hidden" id="sidebar-overlay"></div>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        // Close mobile menu when overlay is clicked
        document.getElementById('sidebar-overlay')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>

    @stack('scripts')
</body>

</html>