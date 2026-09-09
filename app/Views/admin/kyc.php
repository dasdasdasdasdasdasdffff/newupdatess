<?php
/**
 * CapitalNest Nepal - Admin KYC Review & Compliance Desk
 */
use App\Helpers\Formatter;

$status = $status ?? 'all';
$submissions = $submissions ?? [];
$csrf = $csrf ?? '';
$pageTitle = 'KYC Compliance Desk - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">KYC Identity Approvals</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Audit identity documents, biometric selfie verification and verify civil credentials.</p>
    </div>

    <!-- Status filter tabs -->
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
        <a href="/admin/kyc?status=pending" class="px-3 py-1.5 rounded-lg <?= $status === 'pending' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Pending Review
        </a>
        <a href="/admin/kyc?status=verified" class="px-3 py-1.5 rounded-lg <?= $status === 'verified' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Verified
        </a>
        <a href="/admin/kyc?status=rejected" class="px-3 py-1.5 rounded-lg <?= $status === 'rejected' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Rejected
        </a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Investor</th>
                    <th class="px-6 py-3.5">Document Type & ID</th>
                    <th class="px-6 py-3.5">Full Legal Name</th>
                    <th class="px-6 py-3.5">Proof Documents</th>
                    <th class="px-6 py-3.5">Submitted</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Review Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B7280]">
                            No KYC submissions found in the <?= htmlspecialchars($status) ?> queue.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($submissions as $k): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $k['user_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($k['user_name']) ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($k['user_email']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $k['document_type']))) ?></div>
                                <div class="font-mono text-[#6B7280] text-[11px]"><?= htmlspecialchars($k['id_number']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($k['full_name']) ?></div>
                                <div class="text-[10px] text-gray-500">Father: <?= htmlspecialchars($k['father_name'] ?? 'N/A') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <?php if (!empty($k['front_document_path'])): ?>
                                        <?php $frontUrl = '/secure-file?path=' . urlencode($k['front_document_path']); ?>
                                        <?php $frontExt = strtolower(pathinfo($k['front_document_path'], PATHINFO_EXTENSION)); ?>
                                        <a href="<?= htmlspecialchars($frontUrl) ?>" target="_blank" title="Front Document">
                                            <?php if (in_array($frontExt, ['jpg', 'jpeg', 'png', 'webp'], true)): ?>
                                                <img src="<?= htmlspecialchars($frontUrl) ?>" alt="Front document" class="h-12 w-16 rounded-md border border-[#E5E7EB] object-cover hover:opacity-80">
                                            <?php else: ?>
                                                <span class="p-1 bg-gray-100 hover:bg-gray-200 rounded text-[11px] font-semibold text-[#111827] inline-flex items-center space-x-1"><i data-lucide="file-text" class="w-3.5 h-3.5 text-[#C59B27]"></i><span>Front</span></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($k['back_document_path'])): ?>
                                        <?php $backUrl = '/secure-file?path=' . urlencode($k['back_document_path']); ?>
                                        <?php $backExt = strtolower(pathinfo($k['back_document_path'], PATHINFO_EXTENSION)); ?>
                                        <a href="<?= htmlspecialchars($backUrl) ?>" target="_blank" title="Back Document">
                                            <?php if (in_array($backExt, ['jpg', 'jpeg', 'png', 'webp'], true)): ?>
                                                <img src="<?= htmlspecialchars($backUrl) ?>" alt="Back document" class="h-12 w-16 rounded-md border border-[#E5E7EB] object-cover hover:opacity-80">
                                            <?php else: ?>
                                                <span class="p-1 bg-gray-100 hover:bg-gray-200 rounded text-[11px] font-semibold text-[#111827] inline-flex items-center space-x-1"><i data-lucide="file-text" class="w-3.5 h-3.5 text-[#C59B27]"></i><span>Back</span></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($k['selfie_path'])): ?>
                                        <?php $selfieUrl = '/secure-file?path=' . urlencode($k['selfie_path']); ?>
                                        <a href="<?= htmlspecialchars($selfieUrl) ?>" target="_blank" title="Selfie with Document">
                                            <img src="<?= htmlspecialchars($selfieUrl) ?>" alt="KYC selfie" class="h-12 w-12 rounded-full border border-[#E5E7EB] object-cover hover:opacity-80">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($k['submitted_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::kycBadge($k['status']) ?>
                                <?php if ($k['status'] === 'rejected' && !empty($k['rejection_reason'])): ?>
                                    <div class="text-[10px] text-rose-600 mt-1 max-w-xs truncate" title="<?= htmlspecialchars($k['rejection_reason']) ?>">
                                        <?= htmlspecialchars($k['rejection_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($k['status'] === 'pending'): ?>
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Approve form -->
                                        <form action="/admin/kyc/approve" method="POST" class="inline">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="kyc_id" value="<?= $k['id'] ?>">
                                            <button type="submit" onclick="return confirm('Approve KYC verification for <?= addslashes($k['full_name']) ?>?')"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-xs">
                                                Approve
                                            </button>
                                        </form>

                                        <!-- Reject button -->
                                        <button type="button" onclick="openRejectKycModal(<?= $k['id'] ?>, '<?= htmlspecialchars(addslashes($k['full_name'])) ?>')"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg transition">
                                            Reject
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400">Audited</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Reject KYC -->
<div id="rejectKModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-[#E5E7EB] shadow-2xl p-6">
        <h3 class="text-base font-bold text-[#111827]">Reject KYC Verification</h3>
        <p class="text-xs text-[#6B7280] mt-1">Specify reason for rejecting <span id="rejectKUser" class="font-bold text-[#111827]"></span>'s KYC package.</p>

        <form action="/admin/kyc/reject" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="kyc_id" id="rejectKycId" value="">

            <div>
                <label for="rejectKReason" class="block text-xs font-semibold text-[#111827] mb-1">Audit Rejection Reason</label>
                <textarea id="rejectKReason" name="reason" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-rose-500 focus:outline-none"
                    placeholder="e.g. Blurry ID scan, selfie not showing document clearly, or address mismatch..."></textarea>
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeRejectKycModal()" class="flex-1 py-2 px-4 bg-gray-100 text-[#111827] rounded-xl text-xs font-semibold hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition">
                    Submit Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectKycModal(id, user) {
        document.getElementById('rejectKycId').value = id;
        document.getElementById('rejectKUser').innerText = user;
        document.getElementById('rejectKModal').classList.remove('hidden');
    }
    function closeRejectKycModal() {
        document.getElementById('rejectKModal').classList.add('hidden');
    }
</script>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
