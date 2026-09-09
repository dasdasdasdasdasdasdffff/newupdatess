<!DOCTYPE html>
<html lang="en" class="h-full bg-[#111827]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Ops Authentication - CapitalNest Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#111827] text-white flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex justify-center mb-4">
            <div class="w-20 h-20 rounded-full bg-[#111827] text-white flex items-center justify-center text-base font-bold shadow-sm border border-gray-700">CN</div>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-white">
            CapitalNest Ops Command
        </h2>
        <p class="mt-1 text-xs text-gray-400">
            Authorized administrative & compliance personnel only
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-[#1F2937] py-8 px-6 shadow-2xl border border-gray-700 rounded-2xl sm:px-10">

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 bg-rose-950/80 border border-rose-800 text-rose-300 rounded-xl text-xs flex items-center space-x-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-4" action="/admin/login" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-200">Admin Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required
                            placeholder="Enter admin email"
                            class="block w-full px-3.5 py-2.5 bg-[#111827] border border-gray-600 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-200">Security Clearance Key</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required
                            placeholder="Enter your password"
                            class="block w-full px-3.5 py-2.5 bg-[#111827] border border-gray-600 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#C59B27] focus:ring-1 focus:ring-[#C59B27]">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center space-x-2 py-2.5 px-4 border border-transparent rounded-xl text-sm font-bold text-[#111827] bg-[#C59B27] hover:bg-[#B3891E] transition shadow-md">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>Authenticate to Console</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-700 text-center">
                <a href="/login" class="text-xs text-gray-400 hover:text-white transition flex items-center justify-center space-x-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Return to Client Portal</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
