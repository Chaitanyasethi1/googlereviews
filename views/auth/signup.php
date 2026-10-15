<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | <?= env("APP_NAME", "ReviewAI") ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50/40 to-indigo-50/50 min-h-screen flex items-center justify-center p-4 sm:p-6 font-sans text-gray-900">

    <div class="w-full max-w-md">
        <!-- Logo / Brand Badge -->
        <div class="text-center mb-6">
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white shadow-sm border border-gray-100 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                    <i class="fab fa-google"></i>
                </div>
                <div class="text-left">
                    <span class="block text-base font-extrabold font-display tracking-tight text-gray-900 leading-tight">
                        <?= env("APP_NAME", "ReviewAI") ?>
                    </span>
                    <span class="block text-[11px] font-medium text-blue-600 uppercase tracking-wider leading-none">
                        Google Reviews Pro
                    </span>
                </div>
            </a>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-blue-500/5 border border-gray-100 p-8 sm:p-10 relative overflow-hidden">
            <!-- Decorative top accent line -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>

            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-gray-900 tracking-tight">
                    Create Account
                </h1>
                <p class="text-gray-500 text-sm mt-1.5">
                    Start collecting 5-star Google reviews on autopilot
                </p>
            </div>

            <!-- Flash Error Message -->
            <?php if (isset($_SESSION['auth_error'])): ?>
                <div class="mb-6 flex items-start gap-3 p-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-2xl animate-shake" role="alert">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 text-base flex-shrink-0"></i>
                    <div class="flex-1 font-medium leading-relaxed">
                        <?= htmlspecialchars($_SESSION['auth_error']) ?>
                    </div>
                </div>
                <?php unset($_SESSION['auth_error']); ?>
            <?php endif; ?>

            <!-- Form -->
            <form action="<?= url("/api/auth?action=signup") ?>" method="POST" class="space-y-5" id="signupForm">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="far fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" required autocomplete="email"
                            class="w-full pl-11 pr-4 py-3.5 bg-gray-50/70 hover:bg-gray-50 rounded-2xl border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm font-medium text-gray-800 placeholder-gray-400 transition-all"
                            placeholder="name@company.com">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8"
                            class="w-full pl-11 pr-11 py-3.5 bg-gray-50/70 hover:bg-gray-50 rounded-2xl border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm font-medium text-gray-800 placeholder-gray-400 transition-all"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Toggle password visibility">
                            <i class="far fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                        <i class="fas fa-shield-alt text-gray-400"></i> At least 8 characters required.
                    </p>
                </div>

                <!-- Consent -->
                <div class="flex items-start gap-2.5 pt-1">
                    <input id="terms" name="terms" type="checkbox" required class="w-4 h-4 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="terms" class="text-xs text-gray-600 leading-snug">
                        I agree to the 
                        <a href="<?= url('/terms') ?>" class="text-blue-600 font-semibold hover:underline">Terms of Service</a> 
                        and 
                        <a href="<?= url('/privacy') ?>" class="text-blue-600 font-semibold hover:underline">Privacy Policy</a>.
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="w-full mt-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <span id="btnText">Create Account</span>
                    <i class="fas fa-arrow-right text-xs" id="btnIcon"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="<?= url("/login") ?>" class="font-bold text-blue-600 hover:text-blue-700 hover:underline ml-1">
                        Sign in
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to home -->
        <div class="text-center mt-6">
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i>
                Back to homepage
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        document.getElementById('signupForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            btnText.textContent = 'Creating account...';
            btnIcon.className = 'fas fa-spinner fa-spin text-sm';
        });
    </script>
</body>

</html>