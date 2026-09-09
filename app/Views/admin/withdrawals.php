<?php
/**
 * CapitalNest Nepal - Admin Withdrawal Processing & Disbursements
 */
use App\Helpers\Formatter;

$pageTitle = 'Disbursements & Payouts - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Withdrawal Disbursements</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Execute settlement disbursements to client bank accounts or digital wallets. Rejections refund locked balances automatically.</p>
    </div>

    <!-- Status filter tabs -->
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
        <a href="/admin/withdrawals?status=pending" class="px-3 py-1.5 rounded-lg <?= $status === 'pending' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Pending Queue
        </a>
        <a href="/admin/withdrawals?status=completed" class="px-3 py-1.5 rounded-lg <?= $status === 'completed' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Completed
        </a>
        <a href="/admin/withdrawals?status=rejected" class="px-3 py-1.5 rounded-lg <?= $status === 'rejected' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Rejected / Refunded
        </a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Investor</th>
                    <th class="px-6 py-3.5">Channel</th>
                    <th class="px-6 py-3.5">Amount</th>
                    <th class="px-6 py-3.5">Beneficiary Bank & Account</th>
                    <th class="px-6 py-3.5">Requested At</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B7280]">
                            No withdrawal entries found in the <?= htmlspecialchars($status) ?> queue.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($withdrawals as $w): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $w['user_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($w['user_name']) ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($w['user_email']) ?></div>
                                <div class="text-[10px] font-mono text-[#9CA3AF]"><?= htmlspecialchars($w['withdrawal_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-[#111827]"><?= htmlspecialchars($w['method']) ?></span>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-sm text-rose-600">
                                <?= Formatter::currency((float)$w['amount']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($w['account_name']) ?></div>
                                <div class="font-mono text-[#6B7280]"><?= htmlspecialchars($w['account_number']) ?></div>
                                <?php if (!empty($w['bank_name'])): ?>
                                    <div class="text-[10px] text-gray-500"><?= htmlspecialchars($w['bank_name']) ?> <?= !empty($w['branch_name']) ? ' &bull; ' . htmlspecialchars($w['branch_name']) : '' ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($w['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($w['status']) ?>
                                <?php if ($w['status'] === 'rejected' && !empty($w['rejection_reason'])): ?>
                                    <div class="text-[10px] text-rose-600 mt-1 max-w-xs truncate" title="<?= htmlspecialchars($w['rejection_reason']) ?>">
                                        <?= htmlspecialchars($w['rejection_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($w['status'] === 'pending' || $w['status'] === 'processing'): ?>
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Approve form -->
                                        <form action="/admin/withdrawals/approve" method="POST" class="inline">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="withdrawal_id" value="<?= $w['id'] ?>">
                                            <button type="submit" onclick="return confirm('Confirm disbursement settlement of <?= Formatter::currency((float)$w['amount']) ?> to <?= addslashes($w['account_name']) ?>?')"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-xs">
                                                Complete
                                            </button>
                                        </form>

                                        <!-- Reject button (modal trigger) -->
                                        <button type="button" onclick="openRejectWithdrawalModal(<?= $w['id'] ?>, '<?= htmlspecialchars(addslashes($w['account_name'])) ?>', '<?= Formatter::currency((float)$w['amount']) ?>')"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg transition">
                                            Reject & Refund
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400">Archived</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Reject & Refund Withdrawal -->
<div id="rejectWModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-[#E5E7EB] shadow-2xl p-6">
        <h3 class="text-base font-bold text-[#111827]">Reject & Refund Disbursement</h3>
        <p class="text-xs text-[#6B7280] mt-1">This will unlock and return <span id="rejectWAmount" class="font-bold text-[#111827]"></span> to <span id="rejectWUser" class="font-bold text-[#111827]"></span>'s available liquid balance.</p>

        <form action="/admin/withdrawals/reject" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="withdrawal_id" id="rejectWId" value="">

            <div>
                <label for="rejectWReason" class="block text-xs font-semibold text-[#111827] mb-1">Rejection Reason</label>
                <textarea id="rejectWReason" name="reason" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-rose-500 focus:outline-none"
                    placeholder="e.g. Beneficiary account name does not match KYC, invalid account number..."></textarea>
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeRejectWModal()" class="flex-1 py-2 px-4 bg-gray-100 text-[#111827] rounded-xl text-xs font-semibold hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition">
                    Confirm Refund
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectWithdrawalModal(id, user, amount) {
        document.getElementById('rejectWId').value = id;
        document.getElementById('rejectWUser').innerText = user;
        document.getElementById('rejectWAmount').innerText = amount;
        document.getElementById('rejectWModal').classList.remove('hidden');
    }
    function closeRejectWModal() {
        document.getElementById('rejectWModal').classList.add('hidden');
    }
</script>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
