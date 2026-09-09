<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Account - CapitalNest Nepal</title>
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
            Join CapitalNest Nepal
        </h2>
        <p class="mt-1 text-center text-xs text-[#6B7280]">
            Open an institutional or retail investment account
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

            <form class="space-y-4" action="/register" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label for="name" class="block text-xs font-semibold text-[#111827]">Full Legal Name</label>
                    <div class="mt-1">
                        <input id="name" name="name" type="text" required
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]"
                            placeholder="e.g. Ramesh Kumar Shrestha">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-[#111827]">Email Address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]"
                            placeholder="ramesh@example.com">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-[#111827]">Mobile Number (Nepal)</label>
                    <div class="mt-1">
                        <input id="phone" name="phone" type="tel" required
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]"
                            placeholder="98XXXXXXXX">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#111827]">Create Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required minlength="8"
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]"
                            placeholder="Min. 8 characters">
                    </div>
                </div>

                <div>
                    <label for="referral_code" class="block text-xs font-semibold text-[#111827]">Referral Code (Optional)</label>
                    <div class="mt-1">
                        <input id="referral_code" name="referral_code" type="text"
                            value="<?= htmlspecialchars($ref ?? '') ?>"
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27] uppercase"
                            placeholder="e.g. NEPAL88">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center space-x-2 py-2.5 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-[#111827] hover:bg-[#1F2937] transition shadow-sm">
                        <span>Register & Create Wallet</span>
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-xs text-[#6B7280]">
                Already have an account?
                <a href="/login" class="font-semibold text-[#111827] hover:underline ml-1">Sign In</a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
