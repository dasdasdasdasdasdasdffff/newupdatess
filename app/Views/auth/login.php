<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - CapitalNest Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#F8F9FA] text-[#111827] flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center mb-4">
            <div class="w-20 h-20 rounded-full bg-[#111827] text-white flex items-center justify-center text-base font-bold shadow-sm">CN</div>
        </div>
        <h2 class="text-center text-2xl font-bold tracking-tight text-[#111827]">
            CAPITAL<span class="text-[#C59B27]">NEST</span> NEPAL
        </h2>
        <p class="mt-1 text-center text-xs text-[#6B7280]">
            Premier FinTech & Wealth Management Portal
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-6 shadow-sm border border-[#E5E7EB] rounded-2xl sm:px-10">

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center space-x-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-4" action="/login" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label for="email" class="block text-xs font-semibold text-[#111827]">Email Address</label>
                    <div class="mt-1.5 relative">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27] transition"
                            placeholder="name@capitalnest.np">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#111827]">Account Password</label>
                    <div class="mt-1.5 relative">
                        <input id="password" name="password" type="password" required
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27] transition"
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox"
                            class="h-4 w-4 text-[#C59B27] focus:ring-[#C59B27] border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-[#6B7280]">Keep me authenticated</label>
                    </div>
                    <a href="/forgot-password" class="text-xs font-semibold text-[#C59B27] hover:underline">Forgot password?</a>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center space-x-2 py-2.5 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-[#111827] hover:bg-[#1F2937] active:scale-[0.99] transition shadow-sm">
                        <span>Access Investment Portal</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-xs text-[#6B7280]">
                Don't have an account yet?
                <a href="/register" class="font-semibold text-[#111827] hover:underline ml-1">Create an Account</a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
