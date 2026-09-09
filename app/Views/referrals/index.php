<?php
/**
 * CapitalNest Nepal - Referral Partner Network & Commission Ledger
 */
use App\Helpers\Formatter;

$pageTitle = 'Referral Network - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Referral Partnership Program</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Refer a new partner and receive NPR 100. New partners registering with your code receive NPR 50 directly in their main balance.</p>
</div>

<!-- Referral Link Card -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280]">Your Dedicated Partner Link</span>
            <div class="flex items-center space-x-2 mt-1">
                <input id="refLinkInput" type="text" readonly value="<?= htmlspecialchars($referralLink) ?>"
                    class="w-full sm:w-96 px-3.5 py-2.5 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs font-mono text-[#111827] focus:outline-none">
                <button type="button" onclick="copyRefLink()" class="px-4 py-2.5 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 flex-shrink-0">
                    <i data-lucide="copy" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                    <span id="copyBtnText">Copy Link</span>
                </button>
            </div>
        </div>

        <div class="p-4 bg-[#FEF9EE] border border-[#F3E8C6] rounded-xl text-xs flex items-center space-x-4">
            <div>
                <span class="text-[10px] text-[#6B7280] uppercase tracking-wider font-semibold">Your Referral Code</span>
                <div class="text-xl font-extrabold text-[#111827] tracking-wider"><?= htmlspecialchars($summary['referral_code']) ?></div>
            </div>
            <div class="border-l border-[#F3E8C6] pl-4">
                <span class="text-[10px] text-[#6B7280] uppercase tracking-wider font-semibold">Registration Rewards</span>
                <div class="text-xl font-extrabold text-[#C59B27]">NPR 100 + NPR 50</div>
            </div>
        </div>
    </div>
</div>

<!-- Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Registered Partners</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= $summary['total_referrals'] ?></div>
        <span class="text-[11px] text-[#6B7280]">Invited via your code</span>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Active Investing Partners</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= $summary['active_referrals'] ?></div>
        <span class="text-[11px] text-emerald-600 font-medium">Yielding recurring commissions</span>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Referral Earnings Paid</span>
        <div class="mt-2 text-2xl font-bold text-[#C59B27]"><?= Formatter::currency((float)$summary['total_earnings']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Audited into wallet ledger</span>
    </div>
</div>

<!-- Partner Directory -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Referred Partners</h2>
        <p class="text-xs text-[#6B7280]">List of accounts registered with your code.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Partner Name</th>
                    <th class="px-6 py-3.5">Contact Email</th>
                    <th class="px-6 py-3.5">Joined Date</th>
                    <th class="px-6 py-3.5">Referral Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($summary['referrals'])): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-[#6B7280]">
                            No partners joined using your code yet. Share your partner link to start earning!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($summary['referrals'] as $ref): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-[#111827]">
                                <?= htmlspecialchars($ref['name']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= htmlspecialchars($ref['email']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::date($ref['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($ref['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Earnings Ledger -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Commission Inflow History</h2>
        <p class="text-xs text-[#6B7280]">Itemized commissions earned on investments made by your referred partners.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Source Partner</th>
                    <th class="px-6 py-3.5">Partner Invested</th>
                    <th class="px-6 py-3.5">Tier Rate</th>
                    <th class="px-6 py-3.5">Commission Earned</th>
                    <th class="px-6 py-3.5">Credited At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($summary['earnings'])): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#6B7280]">
                            No commission payouts recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($summary['earnings'] as $ern): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-[#111827]">
                                <?= htmlspecialchars($ern['from_user_name'] ?? 'Partner') ?>
                            </td>
                            <td class="px-6 py-4 font-mono text-[#6B7280]">
                                <?= Formatter::currency((float)$ern['investment_amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-bold text-[#C59B27]">
                                <?= htmlspecialchars($ern['commission_rate']) ?>%
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                +<?= Formatter::currency((float)$ern['commission_amount']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($ern['created_at']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function copyRefLink() {
        const input = document.getElementById('refLinkInput');
        input.select();
        navigator.clipboard.writeText(input.value);
        document.getElementById('copyBtnText').innerText = 'Copied!';
        setTimeout(() => {
            document.getElementById('copyBtnText').innerText = 'Copy Link';
        }, 2000);
    }
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
