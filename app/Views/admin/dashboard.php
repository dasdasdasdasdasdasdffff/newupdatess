<?php
/**
 * CapitalNest Nepal - Master Admin Operations Dashboard
 */
use App\Helpers\Formatter;

$pageTitle = 'Ops Center - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Treasury & Compliance Operations</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Real-time metrics calculated from database balances and transaction ledgers.</p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="/admin/deposits" class="px-3.5 py-1.5 bg-[#111827] text-white rounded-xl text-xs font-semibold hover:bg-gray-800 transition inline-flex items-center space-x-1.5">
            <span class="w-2 h-2 rounded-full <?= $pendingDeposits > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400' ?>"></span>
            <span>Deposits (<?= $pendingDeposits ?>)</span>
        </a>
        <a href="/admin/withdrawals" class="px-3.5 py-1.5 bg-[#111827] text-white rounded-xl text-xs font-semibold hover:bg-gray-800 transition inline-flex items-center space-x-1.5">
            <span class="w-2 h-2 rounded-full <?= $pendingWithdrawals > 0 ? 'bg-rose-400 animate-pulse' : 'bg-emerald-400' ?>"></span>
            <span>Withdrawals (<?= $pendingWithdrawals ?>)</span>
        </a>
        <a href="/admin/kyc" class="px-3.5 py-1.5 bg-[#111827] text-white rounded-xl text-xs font-semibold hover:bg-gray-800 transition inline-flex items-center space-x-1.5">
            <span class="w-2 h-2 rounded-full <?= $pendingKyc > 0 ? 'bg-blue-400 animate-pulse' : 'bg-emerald-400' ?>"></span>
            <span>KYC Queue (<?= $pendingKyc ?>)</span>
        </a>
    </div>
</div>

<!-- Primary Financial Telemetry (8 Cards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Inflow (Deposits) -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Settled Deposits</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency($totalDeposited) ?></div>
        <div class="mt-1 text-[11px] text-[#6B7280]">Realized fiat capital inflow</div>
    </div>

    <!-- Total Outflow (Withdrawals) -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Withdrawn / Disbursed</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency($totalWithdrawn) ?></div>
        <div class="mt-1 text-[11px] text-[#6B7280]">Completed banking disbursements</div>
    </div>

    <!-- Active Capital Invested -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Active Invested Capital</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency($totalInvested) ?></div>
        <div class="mt-1 text-[11px] text-[#6B7280]">Locked in plan tranches</div>
    </div>

    <!-- Cumulative Yield Realized -->
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Investor Profits</span>
        <div class="mt-2 text-2xl font-bold text-[#C59B27]"><?= Formatter::currency($totalProfits) ?></div>
        <div class="mt-1 text-[11px] text-[#6B7280]">Accrued & credited returns</div>
    </div>
</div>

<!-- Operational Queues & User Totals -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Total Investors</span>
            <span class="text-xs font-bold text-emerald-600"><?= $activeUsers ?> active</span>
        </div>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= $totalUsers ?></div>
        <a href="/admin/users" class="text-[11px] text-[#C59B27] font-semibold hover:underline mt-1 block">Manage Investors &rarr;</a>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Pending Deposits</span>
            <span class="w-2.5 h-2.5 rounded-full <?= $pendingDeposits > 0 ? 'bg-amber-500' : 'bg-gray-300' ?>"></span>
        </div>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= $pendingDeposits ?></div>
        <a href="/admin/deposits" class="text-[11px] text-[#C59B27] font-semibold hover:underline mt-1 block">Review Queue &rarr;</a>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Pending Withdrawals</span>
            <span class="w-2.5 h-2.5 rounded-full <?= $pendingWithdrawals > 0 ? 'bg-rose-500' : 'bg-gray-300' ?>"></span>
        </div>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= $pendingWithdrawals ?></div>
        <a href="/admin/withdrawals" class="text-[11px] text-[#C59B27] font-semibold hover:underline mt-1 block">Disburse Queue &rarr;</a>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[#6B7280]">Pending KYC Submissions</span>
            <span class="w-2.5 h-2.5 rounded-full <?= $pendingKyc > 0 ? 'bg-blue-500' : 'bg-gray-300' ?>"></span>
        </div>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= $pendingKyc ?></div>
        <a href="/admin/kyc" class="text-[11px] text-[#C59B27] font-semibold hover:underline mt-1 block">Verify Documents &rarr;</a>
    </div>
</div>

<!-- Pending Operations Quick Action Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

    <!-- Recent Pending Deposits Queue -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Treasury Deposit Queue</h2>
                <p class="text-xs text-[#6B7280]">Vouchers awaiting treasury clearance</p>
            </div>
            <a href="/admin/deposits" class="text-xs font-semibold text-[#C59B27] hover:underline">View All &rarr;</a>
        </div>

        <?php if (empty($recentDeposits)): ?>
            <div class="py-8 text-center text-xs text-[#6B7280]">No recent deposits in queue.</div>
        <?php else: ?>
            <div class="divide-y divide-[#F3F4F6] text-xs">
                <?php foreach ($recentDeposits as $d): ?>
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-[#111827]"><?= htmlspecialchars($d['user_name']) ?></div>
                            <div class="text-[10px] text-[#6B7280] font-mono"><?= htmlspecialchars($d['payment_method']) ?> &bull; Ref: <?= htmlspecialchars($d['reference_number'] ?? 'N/A') ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold font-mono text-[#111827]"><?= Formatter::currency((float)$d['amount']) ?></div>
                            <div><?= Formatter::statusBadge($d['status']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Pending Withdrawals Queue -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Disbursement Queue</h2>
                <p class="text-xs text-[#6B7280]">Withdrawal requests requiring bank settlement</p>
            </div>
            <a href="/admin/withdrawals" class="text-xs font-semibold text-[#C59B27] hover:underline">View All &rarr;</a>
        </div>

        <?php if (empty($recentWithdrawals)): ?>
            <div class="py-8 text-center text-xs text-[#6B7280]">No recent disbursements in queue.</div>
        <?php else: ?>
            <div class="divide-y divide-[#F3F4F6] text-xs">
                <?php foreach ($recentWithdrawals as $w): ?>
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-[#111827]"><?= htmlspecialchars($w['user_name']) ?></div>
                            <div class="text-[10px] text-[#6B7280] font-mono"><?= htmlspecialchars($w['method']) ?> &bull; <?= htmlspecialchars($w['account_number']) ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold font-mono text-rose-600"><?= Formatter::currency((float)$w['amount']) ?></div>
                            <div><?= Formatter::statusBadge($w['status']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
