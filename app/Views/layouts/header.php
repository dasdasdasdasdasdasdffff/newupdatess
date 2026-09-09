<?php
/**
 * CapitalNest Nepal - User Portal Layout Header
 */
use App\Helpers\Formatter;
use App\Services\AuthService;
use App\Services\KycService;

$currentUser = AuthService::getCurrentUser();
$kycRecord = $currentUser ? (new KycService())->getUserKyc((int)$currentUser['id']) : null;
$kycStatus = $kycRecord['status'] ?? 'not_submitted';
$activeRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CapitalNest Nepal - Premier FinTech') ?></title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
    </style>
</head>
<body class="h-full bg-[#F8F9FA] text-[#111827] flex flex-col">

    <!-- Top Sticky Financial Header -->
    <header class="bg-white border-b border-[#E5E7EB] sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="/dashboard" class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-full bg-[#111827] text-white flex items-center justify-center text-xs font-bold">CN</div>
                        <div>
                            <span class="text-base font-bold tracking-tight text-[#111827]">CAPITAL<span class="text-[#C59B27]">NEST</span></span>
                            <span class="block text-[10px] uppercase tracking-widest text-[#6B7280] font-semibold -mt-1">NEPAL FINTECH</span>
                        </div>
                    </a>
                </div>

                <!-- Live User Snapshot -->
                <?php if ($currentUser): ?>
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Available Balance Badge -->
                    <a href="/wallet" class="hidden sm:flex items-center space-x-2 px-3 py-1.5 bg-[#FEF9EE] border border-[#F3E8C6] rounded-xl hover:bg-[#FDF4DC] transition">
                        <i data-lucide="wallet" class="w-4 h-4 text-[#C59B27]"></i>
                        <span class="text-xs text-[#6B7280]">Available:</span>
                        <span class="text-xs font-bold text-[#111827]"><?= Formatter::currency((float)($currentUser['available_balance'] ?? 0)) ?></span>
                    </a>

                    <!-- KYC Status -->
                    <a href="/kyc" class="hidden md:inline-flex items-center">
                        <?= Formatter::kycBadge($kycStatus) ?>
                    </a>

                    <!-- Notifications Bell -->
                    <a href="/notifications" class="relative p-2 text-[#6B7280] hover:text-[#111827] hover:bg-gray-100 rounded-xl transition">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </a>

                    <!-- User Menu Dropdown trigger -->
                    <div class="flex items-center space-x-2 border-l border-[#E5E7EB] pl-3">
                        <div class="w-8 h-8 rounded-xl bg-gray-100 border border-[#E5E7EB] flex items-center justify-center text-xs font-bold text-[#111827]">
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="hidden lg:block text-left">
                            <div class="text-xs font-semibold text-[#111827] leading-none"><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
                            <div class="text-[10px] text-[#6B7280] leading-tight mt-0.5"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                        </div>
                        <a href="/logout" title="Sign Out" class="p-1.5 text-[#6B7280] hover:text-[#C59B27] rounded-lg transition ml-1">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Primary Portal Navigation Links -->
        <?php if ($currentUser): ?>
        <nav class="bg-white border-t border-[#F3F4F6] overflow-x-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-1 sm:space-x-2 py-2">
                <?php
                $navItems = [
                    ['/dashboard', 'layout-dashboard', 'Dashboard'],
                    ['/wallet', 'wallet', 'Wallet & Ledger'],
                    ['/investments/plans', 'trending-up', 'Investment Plans'],
                    ['/investments/my', 'pie-chart', 'My Portfolio'],
                    ['/deposits', 'arrow-down-circle', 'Deposit'],
                    ['/withdrawals', 'arrow-up-circle', 'Withdraw'],
                    ['/kyc', 'file-badge-2', 'KYC Compliance'],
                    ['/referrals', 'users', 'Referral Network'],
                    ['/support', 'life-buoy', 'Support Desk'],
                ];
                foreach ($navItems as [$url, $icon, $label]):
                    $isActive = ($activeRoute === $url || ($url === '/investments/plans' && $activeRoute === '/investments'));
                ?>
                <a href="<?= $url ?>" class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition <?= $isActive ? 'bg-[#111827] text-white' : 'text-[#6B7280] hover:text-[#111827] hover:bg-gray-100' ?>">
                    <i data-lucide="<?= $icon ?>" class="w-3.5 h-3.5"></i>
                    <span><?= $label ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php endif; ?>
    </header>

    <!-- Main Dynamic Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Flash Alerts -->
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
