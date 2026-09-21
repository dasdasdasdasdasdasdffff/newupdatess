<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-5GTWZLG5');
    </script>
    <!-- End Google Tag Manager -->
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
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5GTWZLG5"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="sm:mx-auto sm:w-full sm:max-w-lg px-4">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                <i data-lucide="mail-check" class="w-7 h-7"></i>
            </div>

            <h1 class="mt-6 text-2xl font-bold tracking-tight text-[#111827]">Waiting for email verification</h1>
            <p class="mt-3 text-sm text-[#6B7280]">
                We have sent a verification link to <span class="font-semibold text-[#111827]"><?= htmlspecialchars($email ?? '') ?></span>.
            </p>
            <?php if (!empty($success)): ?>
                <p class="mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <p class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

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
            <?php if (!empty($email)): ?>
                <a href="/resend-verification?email=<?= urlencode($email) ?>" class="mt-5 inline-block text-xs font-semibold text-[#C59B27] hover:underline">
                    Resend verification email
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
