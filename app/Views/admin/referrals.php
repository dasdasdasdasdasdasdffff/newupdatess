<?php
/**
 * CapitalNest Nepal - Admin Referral Network & Downline Analytics
 */
use App\Helpers\Formatter;

$pageTitle = 'Partner Referral Program - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Referral Partner Architecture</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Audit tier allocations, top partner affiliates, downline registrations and commission payouts.</p>
</div>

<!-- Commission Inflows Table -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Commission Inflow Records</h2>
        <p class="text-xs text-[#6B7280]">Real-time itemized commissions credited to partners upon downline investments.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Referrer Partner</th>
                    <th class="px-6 py-3.5">Referred User (Downline)</th>
                    <th class="px-6 py-3.5">Capital Invested</th>
                    <th class="px-6 py-3.5">Tier Rate</th>
                    <th class="px-6 py-3.5">Commission Credited</th>
                    <th class="px-6 py-3.5">Dispatched At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($earnings)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-[#6B7280]">
                            No referral commission records found in the database.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($earnings as $e): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $e['referrer_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($e['referrer_name']) ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($e['referrer_email']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $e['referred_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($e['referred_name']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 font-mono text-[#6B7280]">
                                <?= Formatter::currency((float)$e['investment_amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-bold text-[#C59B27]">
                                <?= htmlspecialchars($e['commission_rate']) ?>%
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                +<?= Formatter::currency((float)$e['commission_amount']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($e['created_at']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
