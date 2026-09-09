<?php
/**
 * CapitalNest Nepal - Enterprise Admin Layout Header
 */
use App\Helpers\Formatter;
use App\Services\AuthService;

$currentAdmin = AuthService::getCurrentAdmin();
$activeRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CapitalNest Admin Console') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cn: {
                            gold: '#C59B27',
                            'gold-hover': '#B3891E',
                            'gold-light': '#FEF9EE',
                            'gold-border': '#F3E8C6',
                            dark: '#111827',
                            muted: '#6B7280',
                            border: '#E5E7EB',
                            card: '#FFFFFF',
                            bg: '#F8F9FA'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#F8F9FA] text-[#111827] flex flex-col">

    <!-- Admin Top Header -->
    <header class="bg-[#111827] text-white sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="/admin/dashboard" class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-full bg-[#111827] text-white flex items-center justify-center text-xs font-bold border border-gray-700">CN</div>
                        <div>
                            <span class="text-sm font-bold tracking-tight text-white">CAPITAL<span class="text-[#C59B27]">NEST</span></span>
                            <span class="block text-[9px] uppercase tracking-widest text-[#9CA3AF] font-bold -mt-0.5">ADMIN OPS COMMAND</span>
                        </div>
                    </a>
                    <span class="ml-3 px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded bg-[#FEF9EE] text-[#C59B27] border border-[#F3E8C6]">
                        <?= strtoupper($currentAdmin['role'] ?? 'ADMIN') ?>
                    </span>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/dashboard" target="_blank" class="hidden sm:inline-flex items-center space-x-1 text-xs text-gray-300 hover:text-white transition">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>View Client Portal</span>
                    </a>

                    <div class="flex items-center space-x-2 border-l border-gray-700 pl-3">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-semibold text-white"><?= htmlspecialchars($currentAdmin['name'] ?? 'Officer') ?></div>
                            <div class="text-[10px] text-gray-400"><?= htmlspecialchars($currentAdmin['email'] ?? '') ?></div>
                        </div>
                        <a href="/admin/logout" title="Exit Admin Console" class="p-1.5 text-gray-400 hover:text-[#C59B27] rounded-lg transition ml-1">
                            <i data-lucide="power" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Navigation Subbar -->
        <nav class="bg-[#1F2937] border-t border-gray-700 overflow-x-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-1 sm:space-x-2 py-2">
                <?php
                $adminNav = [
                    ['/admin/dashboard', 'activity', 'Ops Center'],
                    ['/admin/users', 'users', 'All Users'],
                    ['/admin/deposits', 'arrow-down-circle', 'Deposits Queue'],
                    ['/admin/withdrawals', 'arrow-up-circle', 'Withdrawals Queue'],
                    ['/admin/kyc', 'file-check', 'KYC Compliance'],
                    ['/admin/investments', 'briefcase', 'Investment Plans'],
                    ['/admin/transactions', 'list-checks', 'Ledger Audit'],
                    ['/admin/referrals', 'git-branch', 'Referral Network'],
                    ['/admin/support', 'message-square', 'Support Desk'],
                    ['/admin/notifications', 'bell', 'Broadcasts'],
                    ['/admin/activity-logs', 'shield', 'Activity Logs'],
                    ['/admin/settings', 'sliders', 'Settings'],
                ];
                foreach ($adminNav as [$url, $icon, $label]):
                    $isActive = ($activeRoute === $url);
                ?>
                <a href="<?= $url ?>" class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition <?= $isActive ? 'bg-[#C59B27] text-[#111827] font-bold' : 'text-gray-300 hover:text-white hover:bg-gray-800' ?>">
                    <i data-lucide="<?= $icon ?>" class="w-3.5 h-3.5"></i>
                    <span><?= $label ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </nav>
    </header>

    <!-- Main Admin Dynamic Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <?php if (!empty($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center space-x-3 text-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($_GET['success']) ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center space-x-3 text-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>
