<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - CapitalNest Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#F8F9FA] text-[#111827] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg px-4">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                <i data-lucide="mail-check" class="w-7 h-7"></i>
            </div>

            <h1 class="mt-6 text-2xl font-bold tracking-tight text-[#111827]">Waiting for email verification</h1>
            <p class="mt-3 text-sm text-[#6B7280]">
                We have sent a verification link to <span class="font-semibold text-[#111827]"><?= htmlspecialchars($email ?? '') ?></span>.
            </p>

            <div class="mt-6 rounded-xl border border-[#F3E8C6] bg-[#FEF9EE] p-4 text-left text-xs text-[#6B7280]">
                <p class="font-semibold text-[#111827] mb-2">Next steps:</p>
                <ul class="space-y-2 list-disc ml-5">
                    <li>Check your inbox and spam/junk folder.</li>
                    <li>Click the confirmation link to activate your account.</li>
                    <li>Once confirmed, you will be redirected and can sign in normally.</li>
                </ul>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/login" class="inline-flex items-center justify-center rounded-xl border border-[#E5E7EB] bg-white px-4 py-2.5 text-xs font-semibold text-[#111827] hover:bg-[#F9FAFB]">
                    Back to login
                </a>
                <a href="/register" class="inline-flex items-center justify-center rounded-xl bg-[#111827] px-4 py-2.5 text-xs font-semibold text-white hover:bg-[#1F2937]">
                    Use a different email
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
