<?php
/**
 * CapitalNest Nepal - Live User Financial Dashboard
 */
use App\Helpers\Formatter;

$pageTitle = 'Dashboard - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<!-- Welcome Banner / Financial Overview -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Financial Portfolio</h1>
            <p class="text-xs text-[#6B7280] mt-0.5">Real-time ledger audit & earning accruals across capital holdings.</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <a href="/deposits" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <i data-lucide="plus-circle" class="w-4 h-4 text-[#C59B27]"></i>
                <span>Add Deposit</span>
            </a>
            <a href="/investments/plans" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#FEF9EE] hover:bg-[#FDF4DC] text-[#111827] border border-[#F3E8C6] rounded-xl text-xs font-semibold transition">
                <i data-lucide="trending-up" class="w-4 h-4 text-[#C59B27]"></i>
                <span>Explore Plans</span>
            </a>
            <a href="/withdrawals" class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 text-[#111827] border border-[#E5E7EB] rounded-xl text-xs font-semibold transition">
                <i data-lucide="arrow-up-circle" class="w-4 h-4 text-[#6B7280]"></i>
                <span>Withdraw</span>
            </a>
        </div>
    </div>
</div>

<!-- 4-Stat Core Balance Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Net Worth -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm hover:border-[#D1D5DB] transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Total Portfolio Value</span>
            <div class="w-7 h-7 rounded-lg bg-[#FEF9EE] flex items-center justify-center text-[#C59B27]">
                <i data-lucide="wallet-cards" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="mt-3 text-2xl font-bold text-[#111827] tracking-tight">
            <?= Formatter::currency($netWorth) ?>
        </div>
        <div class="mt-1 text-[11px] text-[#6B7280]">
            Liquid Available + Active Invested
        </div>
    </div>

    <!-- Available Balance (Liquid) -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm hover:border-[#D1D5DB] transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Available Balance</span>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="mt-3 text-2xl font-bold text-[#111827] tracking-tight">
            <?= Formatter::currency($availableBalance) ?>
        </div>
        <div class="mt-1 text-[11px] text-emerald-700 font-medium">
            Immediate withdrawal / reinvestment ready
        </div>
    </div>

    <!-- Total Active Invested -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm hover:border-[#D1D5DB] transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Currently Invested</span>
            <div class="w-7 h-7 rounded-lg bg-[#F8F9FA] flex items-center justify-center text-[#111827]">
                <i data-lucide="landmark" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="mt-3 text-2xl font-bold text-[#111827] tracking-tight">
            <?= Formatter::currency($investedBalance) ?>
        </div>
        <div class="mt-1 text-[11px] text-[#6B7280]">
            In <?= $activeCount ?> active asset <?= $activeCount === 1 ? 'tranche' : 'tranches' ?>
        </div>
    </div>

    <!-- Total Profit / Earnings -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm hover:border-[#D1D5DB] transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Total Profit Realized</span>
            <div class="w-7 h-7 rounded-lg bg-[#FEF9EE] flex items-center justify-center text-[#C59B27]">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="mt-3 text-2xl font-bold text-[#C59B27] tracking-tight">
            <?= Formatter::currency($totalProfit) ?>
        </div>
        <div class="mt-1 text-[11px] text-[#6B7280]">
            Referral: <?= Formatter::currency($referralSummary['total_earnings'] ?? 0) ?>
        </div>
    </div>
</div>

<!-- Secondary Section: Active Investments & Ledger -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Active Investments Tranches (2 Columns on large) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Active Investment Holdings</h2>
                    <p class="text-xs text-[#6B7280]">Live progress, maturity calendars & accrued returns</p>
                </div>
                <a href="/investments/my" class="text-xs font-semibold text-[#C59B27] hover:underline">View All &rarr;</a>
            </div>

            <?php if (empty($activeInvestments)): ?>
                <div class="py-10 text-center border border-dashed border-[#E5E7EB] rounded-xl">
                    <div class="w-10 h-10 mx-auto rounded-xl bg-gray-50 flex items-center justify-center text-[#9CA3AF] mb-2">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <div class="text-sm font-semibold text-[#111827]">No active investments yet</div>
                    <p class="text-xs text-[#6B7280] max-w-sm mx-auto mt-1 mb-4">
                        Allocate your available balance to verified treasury bonds, hydro funds, and fixed return plans.
                    </p>
                    <a href="/investments/plans" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#111827] text-white rounded-xl text-xs font-semibold">
                        <span>Browse Investment Plans</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($activeInvestments, 0, 3) as $inv): ?>
                        <div class="p-4 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl hover:border-[#D1D5DB] transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2.5">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-bold text-[#111827]"><?= htmlspecialchars($inv['plan_name'] ?? 'Investment Tranche') ?></span>
                                        <span class="px-2 py-0.5 bg-[#FEF9EE] border border-[#F3E8C6] text-[#C59B27] text-[10px] font-bold rounded">
                                            +<?= htmlspecialchars($inv['return_rate'] ?? '0') ?>% Return
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-[#6B7280] mt-0.5">
                                        Ref: <?= htmlspecialchars($inv['investment_ref']) ?> &bull; Started: <?= Formatter::date($inv['start_date']) ?>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <div class="text-sm font-bold text-[#111827]"><?= Formatter::currency((float)$inv['amount']) ?></div>
                                    <div class="text-[11px] text-[#C59B27] font-semibold">Expected: <?= Formatter::currency((float)$inv['expected_return']) ?></div>
                                </div>
                            </div>

                            <!-- Progress bar -->
                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#C59B27] h-1.5 rounded-full" style="width: <?= min(100, max(5, (float)($inv['progress_percent'] ?? 0))) ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] text-[#6B7280] mt-1.5">
                                <span>Progress: <?= number_format((float)($inv['progress_percent'] ?? 0), 1) ?>%</span>
                                <span>Maturity: <?= Formatter::date($inv['end_date']) ?> (<?= max(0, (int)($inv['days_remaining'] ?? 0)) ?> days remaining)</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Ledger Audit Trail -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Recent Transactions</h2>
                    <p class="text-xs text-[#6B7280]">Audited balance increments, investments and payouts</p>
                </div>
                <a href="/wallet" class="text-xs font-semibold text-[#C59B27] hover:underline">Complete Ledger &rarr;</a>
            </div>

            <?php if (empty($transactionsData['data'])): ?>
                <div class="py-8 text-center text-xs text-[#6B7280]">
                    No transaction records found on this account ledger.
                </div>
            <?php else: ?>
                <div class="divide-y divide-[#F3F4F6]">
                    <?php foreach ($transactionsData['data'] as $tx): ?>
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 border border-[#E5E7EB] flex items-center justify-center text-xs">
                                    <?= Formatter::transactionTypeBadge($tx['type']) ?>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-[#111827]"><?= htmlspecialchars($tx['reference']) ?></div>
                                    <div class="text-[10px] text-[#6B7280]"><?= Formatter::dateTime($tx['created_at']) ?> &bull; Ref: <?= htmlspecialchars($tx['transaction_ref']) ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold <?= (float)$tx['amount'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= (float)$tx['amount'] >= 0 ? '+' : '' ?><?= Formatter::currency((float)$tx['amount']) ?>
                                </div>
                                <div class="text-[10px] text-[#6B7280]">Bal: <?= Formatter::currency((float)$tx['new_balance']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Sidebar Quick Operations -->
    <div class="space-y-6">

        <!-- KYC Verification Status Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Compliance Clearance</span>
                <?= Formatter::kycBadge($kycStatus) ?>
            </div>
            <?php if ($kycStatus === 'verified'): ?>
                <p class="text-xs text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-100 mb-3">
                    Your institutional KYC clearance is fully verified. Maximum transaction and withdrawal limits are active.
                </p>
            <?php elseif ($kycStatus === 'pending'): ?>
                <p class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-100 mb-3">
                    Your identity documents are currently undergoing review by compliance officers. Expected clearance: within 2 business hours.
                </p>
            <?php else: ?>
                <p class="text-xs text-[#6B7280] mb-3">
                    Submit citizenship, national ID or passport documents to unlock higher withdrawal quotas and institutional plan access.
                </p>
                <a href="/kyc" class="w-full flex justify-center items-center space-x-1.5 py-2 px-3 bg-[#FEF9EE] border border-[#F3E8C6] hover:bg-[#FDF4DC] rounded-xl text-xs font-semibold text-[#111827] transition">
                    <i data-lucide="file-badge" class="w-4 h-4 text-[#C59B27]"></i>
                    <span>Complete Identity Verification</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Referral Snapshot Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Referral Network</span>
                <span class="text-xs font-bold text-[#C59B27]">5.0% Direct Tier</span>
            </div>
            <div class="p-3 bg-[#F8F9FA] rounded-xl border border-[#E5E7EB] mb-3">
                <div class="text-[10px] text-[#6B7280] uppercase tracking-wider font-semibold">Your Referral Code</div>
                <div class="text-base font-extrabold tracking-wider text-[#111827] mt-0.5"><?= htmlspecialchars($referralSummary['referral_code']) ?></div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center text-xs mb-3">
                <div class="p-2.5 bg-gray-50 border border-[#E5E7EB] rounded-lg">
                    <div class="text-base font-bold text-[#111827]"><?= $referralSummary['total_referrals'] ?></div>
                    <div class="text-[10px] text-[#6B7280]">Partners</div>
                </div>
                <div class="p-2.5 bg-gray-50 border border-[#E5E7EB] rounded-lg">
                    <div class="text-base font-bold text-[#C59B27]"><?= Formatter::currency((float)$referralSummary['total_earnings']) ?></div>
                    <div class="text-[10px] text-[#6B7280]">Earned</div>
                </div>
            </div>
            <a href="/referrals" class="text-xs font-semibold text-[#111827] hover:underline flex items-center justify-center space-x-1">
                <span>Manage Downline & Share Link</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!-- Treasury Security Badge -->
        <div class="bg-[#111827] text-white rounded-2xl p-6 shadow-sm">
            <div class="flex items-center space-x-2 text-[#C59B27] mb-2">
                <i data-lucide="lock" class="w-4 h-4"></i>
                <span class="text-xs font-bold uppercase tracking-wider">Treasury Safeguard</span>
            </div>
            <p class="text-xs text-gray-300 leading-relaxed">
                Client capital is held in segregated trust accounts across Class 'A' commercial banks in Nepal. No speculative trading without collateralization.
            </p>
        </div>

    </div>

</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
