<?php
/**
 * CapitalNest Nepal - Master Platform Transaction Ledger (Admin)
 */
use App\Helpers\Formatter;

$pageTitle = 'Master Ledger - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Master Financial Ledger</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Immutable audit trail of all platform balance changes, credits, deductions and commissions.</p>
    </div>

    <!-- Type filter -->
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
        <a href="/admin/transactions" class="px-3 py-1.5 rounded-lg <?= empty($type) ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">All</a>
        <a href="/admin/transactions?type=deposit" class="px-3 py-1.5 rounded-lg <?= ($type === 'deposit') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Deposits</a>
        <a href="/admin/transactions?type=withdrawal" class="px-3 py-1.5 rounded-lg <?= ($type === 'withdrawal') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Withdrawals</a>
        <a href="/admin/transactions?type=investment" class="px-3 py-1.5 rounded-lg <?= ($type === 'investment') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Investments</a>
        <a href="/admin/transactions?type=profit" class="px-3 py-1.5 rounded-lg <?= ($type === 'profit') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Returns</a>
        <a href="/admin/transactions?type=referral" class="px-3 py-1.5 rounded-lg <?= ($type === 'referral') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Referrals</a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Timestamp</th>
                    <th class="px-6 py-3.5">Investor</th>
                    <th class="px-6 py-3.5">Operation Type</th>
                    <th class="px-6 py-3.5">Previous Balance</th>
                    <th class="px-6 py-3.5">Delta Amount</th>
                    <th class="px-6 py-3.5">Closing Balance</th>
                    <th class="px-6 py-3.5">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($transactions['data'])): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B7280]">
                            No ledger entries found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions['data'] as $tx): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-mono font-bold text-[#111827]"><?= htmlspecialchars($tx['transaction_ref']) ?></div>
                                <div class="text-[10px] text-[#6B7280]"><?= Formatter::dateTime($tx['created_at']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $tx['user_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($tx['user_name'] ?? 'User') ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($tx['user_email'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::transactionTypeBadge($tx['type']) ?>
                            </td>
                            <td class="px-6 py-4 font-mono text-[#6B7280]">
                                <?= Formatter::currency((float)$tx['previous_balance']) ?>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold <?= (float)$tx['amount'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                <?= (float)$tx['amount'] >= 0 ? '+' : '' ?><?= Formatter::currency((float)$tx['amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-[#111827]">
                                <?= Formatter::currency((float)$tx['new_balance']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280] max-w-xs truncate" title="<?= htmlspecialchars($tx['reference']) ?>">
                                <?= htmlspecialchars($tx['reference']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($transactions['totalPages'] > 1): ?>
        <div class="p-4 bg-[#F8F9FA] border-t border-[#E5E7EB] flex items-center justify-between text-xs text-[#6B7280]">
            <span>Page <?= $transactions['page'] ?> of <?= $transactions['totalPages'] ?> (<?= $transactions['total'] ?> total entries)</span>
            <div class="flex space-x-2">
                <?php if ($transactions['page'] > 1): ?>
                    <a href="/admin/transactions?page=<?= $transactions['page'] - 1 ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg">Previous</a>
                <?php endif; ?>
                <?php if ($transactions['page'] < $transactions['totalPages']): ?>
                    <a href="/admin/transactions?page=<?= $transactions['page'] + 1 ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
