<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - EVC Project</title>
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

        <!-- OTP Verification Card -->
        <div class="bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-700">
            <div class="text-center mb-6">
                <h2 class="text-xl font-semibold text-white mb-2">Verify OTP</h2>
                <p class="text-gray-400 text-sm">Enter the 6-digit code sent to your email</p>
                @if(isset($email))
                    <p class="text-green-400 text-sm mt-1">{{ $email }}</p>
                @endif
            </div>

            <form action="{{ route('otp.verify') }}" method="POST" class="space-y-6">
                @csrf
                @if(isset($email))
                    <input type="hidden" name="email" value="{{ $email }}">
                @endif
                <input type="hidden" name="otp" id="otpField">

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

                <!-- OTP Input Fields -->
                <div class="flex justify-center space-x-3 mb-6">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text"
                               maxlength="1"
                               class="otp-input w-12 h-14 text-center text-xl font-bold bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                               data-index="{{ $i }}">
                    @endfor
                </div>

                <!-- Resend OTP -->
                <div class="text-center mb-6">
                    <p class="text-gray-400 text-sm mb-2">Didn't receive the code?</p>
                    <button type="button"
                            onclick="resendOTP()"
                            class="text-green-400 hover:text-green-300 text-sm font-medium transition-colors">
                        Resend OTP
                    </button>
                </div>

                <!-- Verify Button -->
                <button
                    type="submit"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-slate-800">
                    Verify OTP
                </button>
            </form>

            <!-- Back to Registration Link -->
            <div class="text-center mt-6 pt-6 border-t border-slate-700">
                <p class="text-gray-400 text-sm">
                    Want to use a different email?
                    <a href="{{ route('register.form') }}" class="text-green-400 hover:text-green-300 transition-colors font-medium">
                        Back to registration
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll(".otp-input");
        const otpField = document.getElementById("otpField");

        inputs.forEach((input, index) => {
            // Handle input
            input.addEventListener("input", (e) => {
                // Only allow digits
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                if (e.target.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                // Update hidden field with complete OTP
                updateOTPField();
            });

            // Handle backspace
            input.addEventListener("keydown", (e) => {
                if (e.key === "Backspace") {
                    if (!e.target.value && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                    } else {
                        e.target.value = '';
                    }
                    updateOTPField();
                }
            });

            // Handle paste
            input.addEventListener("paste", (e) => {
                e.preventDefault();
                const paste = e.clipboardData.getData('text');
                const digits = paste.replace(/[^0-9]/g, '').substring(0, 6);

                for (let i = 0; i < Math.min(digits.length, 6); i++) {
                    inputs[i].value = digits[i];
                }

                // Focus on next empty field or last field
                const nextEmpty = [...inputs].findIndex(input => !input.value);
                if (nextEmpty !== -1) {
                    inputs[nextEmpty].focus();
                } else {
                    inputs[5].focus();
                }

                updateOTPField();
            });
        });

        function updateOTPField() {
            let otp = "";
            inputs.forEach(input => otp += input.value);
            otpField.value = otp;
        }

        function resendOTP() {
            // Add loading state
            const button = event.target;
            const originalText = button.innerText;
            button.innerText = 'Sending...';
            button.disabled = true;

            // Simulate API call (replace with actual resend logic)
            setTimeout(() => {
                button.innerText = originalText;
                button.disabled = false;

                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'bg-green-500/10 border border-green-500 text-green-400 p-3 rounded-lg text-sm mb-4';
                successDiv.innerHTML = '<p>New OTP has been sent to your email.</p>';

                const form = document.querySelector('form');
                form.insertBefore(successDiv, form.firstChild);

                // Remove success message after 3 seconds
                setTimeout(() => {
                    successDiv.remove();
                }, 3000);

                // Clear OTP inputs
                inputs.forEach(input => input.value = '');
                inputs[0].focus();
                updateOTPField();
            }, 2000);
        }

        // Auto-focus first input on page load
        document.addEventListener('DOMContentLoaded', () => {
            inputs[0].focus();
        });
    </script>
</body>
</html>
