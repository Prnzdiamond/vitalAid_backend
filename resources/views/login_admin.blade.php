<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VitalAid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-purple-900 to-indigo-800">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 20px 20px;">
        </div>
    </div>

    <!-- Back to Welcome Button -->
    <div class="absolute top-4 left-4 z-10">
        <a href="{{ route('admin.welcome') }}" class="text-blue-200 hover:text-white transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Back to Welcome
        </a>
    </div>

    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-xl mb-6">
                    <i class="fas fa-heart-pulse text-3xl text-red-500"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">VitalAid</h1>
                <p class="text-blue-200">Admin Portal Login</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-white/20">
                <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                    @csrf

                    <!-- Login Field -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-user mr-2"></i>Username or Email
                        </label>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required
                            class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition duration-200"
                            placeholder="Enter username or email">
                        @error('login')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition duration-200"
                                placeholder="Enter password">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-3 text-gray-300 hover:text-white transition duration-200">
                                <i id="password-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 text-blue-600 bg-white/20 border-white/30 rounded focus:ring-blue-500 focus:ring-2">
                            <span class="ml-2 text-sm text-white">Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-transparent">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Dashboard
                    </button>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-blue-200">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Secure admin access only
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-blue-200">
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