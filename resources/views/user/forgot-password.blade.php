<!DOCTYPE html>
<html lang="en">
<head>
    <                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            minlength="15"
                            maxlength="255"
                            class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all pl-10"
                            placeholder="Enter email address (min 15 chars)"
                            value="{{ old('email') }}">rset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EVC Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white mb-1">EVC Project</h1>
            <p class="text-gray-400 text-sm">Electric Vehicle Charging Management</p>
        </div>

        <!-- Forgot Password Card -->
        <div class="bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-700">
            <div class="text-center mb-6">
                <h2 class="text-xl font-semibold text-white mb-2">Reset Password</h2>
                <p class="text-gray-400 text-sm">Enter your email to receive a password reset link</p>
            </div>

            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-500/10 border border-red-500 text-red-400 p-3 rounded-lg text-sm">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('status'))
                    <div class="bg-green-500/10 border border-green-500 text-green-400 p-3 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all pl-10"
                            placeholder="Enter your email address"
                            value="{{ old('email') }}">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                    </div>
                </div>

                <!-- Send Reset Link Button -->
                <button
                    type="submit"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-slate-800">
                    Send Reset Link
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center mt-6 pt-6 border-t border-slate-700">
                <p class="text-gray-400 text-sm">
                    Remember your password?
                    <a href="{{ route('login') }}" class="text-green-400 hover:text-green-300 transition-colors font-medium">
                        Back to login
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
