<?php
/**
 * CapitalNest Nepal - Support Desk & Ticket System
 */
use App\Helpers\Formatter;

$pageTitle = 'Support Desk - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Institutional Support Desk</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Submit direct inquiry tickets to compliance, treasury, or investment advisory.</p>
    </div>
    <div>
        <button onclick="toggleNewTicketModal(true)" class="px-4 py-2 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-semibold shadow-sm transition inline-flex items-center space-x-1.5">
            <i data-lucide="plus" class="w-4 h-4 text-[#C59B27]"></i>
            <span>Open Support Ticket</span>
        </button>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden mb-12">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Your Tickets</h2>
        <p class="text-xs text-[#6B7280]">Official correspondence thread with operations.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Subject</th>
                    <th class="px-6 py-3.5">Category</th>
                    <th class="px-6 py-3.5">Priority</th>
                    <th class="px-6 py-3.5">Last Updated</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#6B7280]">
                            No support tickets opened. Click "Open Support Ticket" if you require assistance.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/support/view?id=<?= $t['id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]"><?= htmlspecialchars($t['subject']) ?></a>
                                <div class="text-[10px] text-[#9CA3AF] font-mono"><?= htmlspecialchars($t['ticket_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4 capitalize text-[#6B7280]">
                                <?= htmlspecialchars($t['category']) ?>
                            </td>
                            <td class="px-6 py-4 uppercase font-bold text-[10px] <?= $t['priority'] === 'urgent' ? 'text-rose-600' : ($t['priority'] === 'high' ? 'text-amber-600' : 'text-[#6B7280]') ?>">
                                <?= htmlspecialchars($t['priority']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($t['updated_at'] ?? $t['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($t['status']) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/support/view?id=<?= $t['id'] ?>" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-[#111827] font-semibold rounded-lg transition inline-flex items-center space-x-1">
                                    <span>View Thread</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Support Ticket -->
<div id="newTicketModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-[#E5E7EB] shadow-2xl p-6">
        <div class="flex justify-between items-center pb-3 border-b border-[#E5E7EB]">
            <div>
                <h3 class="text-base font-bold text-[#111827]">Open Support Ticket</h3>
                <p class="text-[11px] text-[#6B7280]">Response turnaround within 2 hours during market hours.</p>
            </div>
            <button onclick="toggleNewTicketModal(false)" class="text-gray-400 hover:text-gray-600 p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="/support/create" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <div>
                <label for="subject" class="block text-xs font-semibold text-[#111827] mb-1">Subject</label>
                <input id="subject" name="subject" type="text" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none"
                    placeholder="Brief summary of inquiry...">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-xs font-semibold text-[#111827] mb-1">Category</label>
                    <select id="category" name="category" class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                        <option value="deposit">Deposit Issue</option>
                        <option value="withdrawal">Withdrawal / Payout</option>
                        <option value="investment">Investment Plans</option>
                        <option value="kyc">KYC Verification</option>
                        <option value="account">Account & Security</option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-xs font-semibold text-[#111827] mb-1">Priority</label>
                    <select id="priority" name="priority" class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                        <option value="medium">Normal / Medium</option>
                        <option value="high">High Priority</option>
                        <option value="urgent">Urgent Escalation</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="message" class="block text-xs font-semibold text-[#111827] mb-1">Detailed Description</label>
                <textarea id="message" name="message" rows="4" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none"
                    placeholder="Provide relevant transaction numbers, dates or account specifics..."></textarea>
            </div>

            <div class="pt-2 flex space-x-3">
                <button type="button" onclick="toggleNewTicketModal(false)" class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-[#111827] rounded-xl text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 px-4 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1 shadow-sm">
                    <span>Submit Ticket</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleNewTicketModal(show) {
        document.getElementById('newTicketModal').classList.toggle('hidden', !show);
    }
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
