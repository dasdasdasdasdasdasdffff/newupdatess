<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - CapitalNest Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#F8F9FA] text-[#111827] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-6 shadow-sm border border-[#E5E7EB] rounded-2xl sm:px-10">
            <div class="flex justify-center mb-5">
                <div class="w-12 h-12 rounded-2xl bg-[#111827] flex items-center justify-center text-[#C59B27] shadow-sm">
                    <i data-lucide="lock-keyhole" class="w-6 h-6"></i>
                </div>
            </div>

            <h2 class="text-center text-2xl font-bold tracking-tight text-[#111827]">Create new password</h2>
            <p class="mt-2 text-center text-xs text-[#6B7280]">Choose a strong password for your account.</p>

            <?php if (!empty($_GET['error'] ?? null)): ?>
                <div class="mt-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($_GET['error']) ?></span>
                </div>
            <?php endif; ?>

            <form class="mt-6 space-y-4" action="/reset-password" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#111827]">New Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required minlength="8"
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]"
                            placeholder="Min. 8 characters">
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-xs font-semibold text-[#111827]">Confirm New Password</label>
                    <div class="mt-1">
                        <input id="confirm_password" name="confirm_password" type="password" required minlength="8"
                            class="block w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm placeholder-[#9CA3AF] focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]"
                            placeholder="Re-enter password">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-[#111827] hover:bg-[#1F2937] transition shadow-sm">
                        Reset password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
