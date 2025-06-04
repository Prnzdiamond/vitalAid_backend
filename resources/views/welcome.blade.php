<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - VitalAid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-green-50 via-white to-emerald-100">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(34, 197, 94, 0.3) 1px, transparent 0); background-size: 20px 20px;">
        </div>
    </div>

    <!-- Main Content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-4xl">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-xl mb-6 border-2 border-green-100">
                    <i class="fas fa-heart-pulse text-3xl text-green-600"></i>
                </div>
                <h1 class="text-6xl font-bold text-green-800 mb-4 tracking-tight">
                    VitalAid
                </h1>
                <p class="text-xl text-green-600 mb-2">Admin Dashboard Portal</p>
                <p class="text-green-500">Manage health, community, and sustainability efforts efficiently</p>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
            @endif

            <!-- Main Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-green-100">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-semibold text-green-800 mb-4">Welcome to Admin Portal</h2>
                    <p class="text-green-600">Secure access to manage your VitalAid platform</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('admin.login') }}"
                        class="w-full sm:w-auto bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-4 px-8 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 shadow-lg text-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Dashboard
                    </a>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-green-800 font-medium mb-2">User Management</h3>
                        <p class="text-green-600 text-sm">Manage user accounts and permissions</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-chart-line text-emerald-600 text-xl"></i>
                        </div>
                        <h3 class="text-green-800 font-medium mb-2">Analytics</h3>
                        <p class="text-green-600 text-sm">Monitor platform performance</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shield-alt text-teal-600 text-xl"></i>
                        </div>
                        <h3 class="text-green-800 font-medium mb-2">Security</h3>
                        <p class="text-green-600 text-sm">Secure admin access control</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-green-600">
                    © {{ date('Y') }} VitalAid. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>

</html>