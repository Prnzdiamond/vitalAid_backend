<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VitalAid</title>
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

    <!-- Back to Admin Welcome Button -->
    <div class="absolute top-4 left-4 z-10">
        <a href="{{ route('admin.welcome') }}"
            class="text-green-600 hover:text-green-800 transition duration-200 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Back to Admin Welcome
        </a>
    </div>

    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-xl mb-6 border-2 border-green-100">
                    <i class="fas fa-heart-pulse text-3xl text-green-600"></i>
                </div>
                <h1 class="text-4xl font-bold text-green-800 mb-2">VitalAid</h1>
                <p class="text-green-600">Admin Portal Login</p>
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

            <!-- Login Form -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl p-8 border border-green-100">
                <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                    @csrf

                    <!-- Login Field -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-green-800 mb-2">
                            <i class="fas fa-user mr-2"></i>Username or Email
                        </label>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required
                            class="w-full px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                            placeholder="Enter username or email">
                        @error('login')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-green-800 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                                placeholder="Enter password">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-3 text-green-500 hover:text-green-700 transition duration-200">
                                <i id="password-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 text-green-600 bg-green-50 border-green-300 rounded focus:ring-green-500 focus:ring-2">
                            <span class="ml-2 text-sm text-green-700">Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Dashboard
                    </button>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-green-600">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Secure admin access only
                    </p>
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

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>

</html>