<?php
/**
 * CapitalNest Nepal - Admin Deposit Approvals & Treasury Queue
 */
use App\Helpers\Formatter;

$status = $status ?? 'all';
$csrf = $csrf ?? '';
$pageTitle = 'Deposit Operations - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Deposit Clearances</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Verify inbound banking transfers and digital wallet vouchers. Approvals credit balances automatically.</p>
    </div>

    <!-- Status filter tabs -->
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
        <a href="/admin/deposits?status=pending" class="px-3 py-1.5 rounded-lg <?= $status === 'pending' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Pending Queue
        </a>
        <a href="/admin/deposits?status=approved" class="px-3 py-1.5 rounded-lg <?= $status === 'approved' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Settled
        </a>
        <a href="/admin/deposits?status=rejected" class="px-3 py-1.5 rounded-lg <?= $status === 'rejected' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Rejected
        </a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Investor</th>
                    <th class="px-6 py-3.5">Method</th>
                    <th class="px-6 py-3.5">Amount</th>
                    <th class="px-6 py-3.5">Transaction ID / Proof</th>
                    <th class="px-6 py-3.5">Submitted</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($deposits)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B7280]">
                            No deposit entries found in the <?= htmlspecialchars($status) ?> queue.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deposits as $d): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $d['user_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($d['user_name']) ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($d['user_email']) ?></div>
                                <div class="text-[10px] font-mono text-[#9CA3AF]"><?= htmlspecialchars($d['deposit_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-[#111827]"><?= htmlspecialchars($d['payment_method']) ?></span>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-sm text-[#111827]">
                                <?= Formatter::currency((float)$d['amount']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-mono text-[#111827] font-semibold"><?= htmlspecialchars($d['reference_number'] ?? 'N/A') ?></div>
                                <?php $proofPath = $d['proof_document_path'] ?? ($d['proof_receipt_path'] ?? null); ?>
                                <?php if (!empty($proofPath)): ?>
                                    <?php $proofUrl = '/secure-file?path=' . urlencode($proofPath); ?>
                                    <?php $proofExt = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION)); ?>
                                    <?php if (in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp'], true)): ?>
                                        <a href="<?= htmlspecialchars($proofUrl) ?>" target="_blank" class="mt-2 block" title="Open payment proof">
                                            <img src="<?= htmlspecialchars($proofUrl) ?>" alt="Payment proof" class="h-16 w-24 rounded-lg border border-[#E5E7EB] object-cover hover:opacity-80">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars($proofUrl) ?>" target="_blank"
                                            class="mt-1 inline-flex items-center space-x-1 text-[11px] text-[#C59B27] hover:underline font-semibold">
                                            <i data-lucide="file-text" class="w-3 h-3"></i>
                                            <span>View Slip</span>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-[10px] text-gray-400">No slip attached</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($d['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($d['status']) ?>
                                <?php if ($d['status'] === 'rejected' && !empty($d['rejection_reason'])): ?>
                                    <div class="text-[10px] text-rose-600 mt-1 max-w-xs truncate" title="<?= htmlspecialchars($d['rejection_reason']) ?>">
                                        <?= htmlspecialchars($d['rejection_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($d['status'] === 'pending'): ?>
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Approve form -->
                                        <form action="/admin/deposits/approve" method="POST" class="inline">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="deposit_id" value="<?= $d['id'] ?>">
                                            <button type="submit" onclick="return confirm('Approve deposit of <?= Formatter::currency((float)$d['amount']) ?> for <?= addslashes($d['user_name']) ?>? This immediately credits the user balance and writes to the ledger.')"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-xs">
                                                Approve
                                            </button>
                                        </form>

                                        <!-- Reject button (modal trigger) -->
                                        <button type="button" onclick="openRejectModal(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['user_name'])) ?>', '<?= Formatter::currency((float)$d['amount']) ?>')"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg transition">
                                            Reject
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Reject Deposit with Reason -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-[#E5E7EB] shadow-2xl p-6">
        <h3 class="text-base font-bold text-[#111827]">Reject Deposit Request</h3>
        <p class="text-xs text-[#6B7280] mt-1">Specify audit reason for <span id="rejectUserName" class="font-bold text-[#111827]"></span>'s deposit (<span id="rejectAmount" class="font-mono font-bold text-[#111827]"></span>).</p>

        <form action="/admin/deposits/reject" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="deposit_id" id="rejectDepositId" value="">

            <div>
                <label for="rejectReason" class="block text-xs font-semibold text-[#111827] mb-1">Rejection Reason</label>
                <textarea id="rejectReason" name="reason" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-rose-500 focus:outline-none"
                    placeholder="e.g. Unmatched transaction ID, voucher unreadable, or incorrect transfer amount..."></textarea>
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-2 px-4 bg-gray-100 text-[#111827] rounded-xl text-xs font-semibold hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id, user, amount) {
        document.getElementById('rejectDepositId').value = id;
        document.getElementById('rejectUserName').innerText = user;
        document.getElementById('rejectAmount').innerText = amount;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
