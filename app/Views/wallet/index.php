<?php
/**
 * CapitalNest Nepal - User Wallet & Financial Ledger
 */
use App\Helpers\Formatter;

$pageTitle = 'Wallet & Ledger - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Wallet & Audited Ledger</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Immutable financial ledger tracking all balance credits, debits and payouts.</p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="/deposits" class="px-4 py-2 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-semibold shadow-sm transition inline-flex items-center space-x-1.5">
            <i data-lucide="arrow-down-circle" class="w-4 h-4 text-[#C59B27]"></i>
            <span>Deposit Funds</span>
        </a>
        <a href="/withdrawals" class="px-4 py-2 bg-white hover:bg-gray-50 text-[#111827] border border-[#E5E7EB] rounded-xl text-xs font-semibold shadow-sm transition inline-flex items-center space-x-1.5">
            <i data-lucide="arrow-up-circle" class="w-4 h-4 text-[#6B7280]"></i>
            <span>Request Withdrawal</span>
        </a>
    </div>
</div>

<!-- Ledger Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Available Liquid Balance</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['available_balance']) ?></div>
        <span class="text-[11px] text-emerald-600 font-medium">Ready for deployment</span>
    </div>
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Active Invested Principal</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['invested_balance']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Committed to yield plans</span>
    </div>
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Deposits Approved</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['total_deposits']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Lifetime settled inflows</span>
    </div>
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Withdrawn</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['total_withdrawals']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Disbursed to bank/wallets</span>
    </div>
</div>

<!-- Ledger Filter & Audit Table -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-[#111827]">Audited Account Ledger</h2>
            <p class="text-xs text-[#6B7280]">Every transaction logs prior balance, executed delta, and closing balance.</p>
        </div>

        <!-- Filter tabs -->
        <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
            <a href="/wallet" class="px-3 py-1 rounded-lg <?= empty($type) ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">All</a>
            <a href="/wallet?type=deposit" class="px-3 py-1 rounded-lg <?= ($type === 'deposit') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Deposits</a>
            <a href="/wallet?type=withdrawal" class="px-3 py-1 rounded-lg <?= ($type === 'withdrawal') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Withdrawals</a>
            <a href="/wallet?type=investment" class="px-3 py-1 rounded-lg <?= ($type === 'investment') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Investments</a>
            <a href="/wallet?type=profit" class="px-3 py-1 rounded-lg <?= ($type === 'profit') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Profits</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Reference / Ref ID</th>
                    <th class="px-6 py-3.5">Type</th>
                    <th class="px-6 py-3.5">Previous Balance</th>
                    <th class="px-6 py-3.5">Delta Amount</th>
                    <th class="px-6 py-3.5">New Balance</th>
                    <th class="px-6 py-3.5">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($transactionsData['data'])): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-[#6B7280]">
                            No ledger entries found matching the criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactionsData['data'] as $tx): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($tx['reference']) ?></div>
                                <div class="text-[10px] text-[#9CA3AF] font-mono"><?= htmlspecialchars($tx['transaction_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::transactionTypeBadge($tx['type']) ?>
                            </td>
                            <td class="px-6 py-4 font-mono text-[#6B7280]">
                                <?= Formatter::currency((float)$tx['previous_balance']) ?>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono <?= (float)$tx['amount'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                <?= (float)$tx['amount'] >= 0 ? '+' : '' ?><?= Formatter::currency((float)$tx['amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-[#111827]">
                                <?= Formatter::currency((float)$tx['new_balance']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($tx['created_at']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($transactionsData['totalPages'] > 1): ?>
    <div class="p-4 bg-[#F8F9FA] border-t border-[#E5E7EB] flex items-center justify-between text-xs">
        <span class="text-[#6B7280]">Page <?= $transactionsData['page'] ?> of <?= $transactionsData['totalPages'] ?> (<?= $transactionsData['total'] ?> entries)</span>
        <div class="flex space-x-2">
            <?php if ($transactionsData['page'] > 1): ?>
                <a href="/wallet?page=<?= $transactionsData['page'] - 1 ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php if ($transactionsData['page'] < $transactionsData['totalPages']): ?>
                <a href="/wallet?page=<?= $transactionsData['page'] + 1 ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
