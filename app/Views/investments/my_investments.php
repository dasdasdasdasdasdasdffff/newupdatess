<?php
/**
 * CapitalNest Nepal - User's Active & Completed Investment Portfolio
 */
use App\Helpers\Formatter;

$pageTitle = 'My Portfolio - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Investment Holdings</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Tracking allocated capital, elapsed duration, maturity dates and earned profit.</p>
    </div>
    <div>
        <a href="/investments/plans" class="px-4 py-2 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-semibold shadow-sm transition inline-flex items-center space-x-1.5">
            <i data-lucide="plus" class="w-4 h-4 text-[#C59B27]"></i>
            <span>Explore More Plans</span>
        </a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Active Portfolio Tranches</h2>
        <p class="text-xs text-[#6B7280]">All investments undergo automated daily accrual and return payout upon maturity.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Plan & Reference</th>
                    <th class="px-6 py-3.5">Capital Principal</th>
                    <th class="px-6 py-3.5">Return Rate</th>
                    <th class="px-6 py-3.5">Expected Total</th>
                    <th class="px-6 py-3.5">Progress / Maturity</th>
                    <th class="px-6 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($investments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-[#6B7280]">
                            No investment records found. Visit <a href="/investments/plans" class="text-[#C59B27] font-semibold hover:underline">Investment Plans</a> to allocate capital.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($investments as $inv): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($inv['plan_name'] ?? 'Asset Holding') ?></div>
                                <div class="text-[10px] text-[#9CA3AF] font-mono"><?= htmlspecialchars($inv['investment_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-[#111827]">
                                <?= Formatter::currency((float)$inv['amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-bold text-[#C59B27]">
                                <?= htmlspecialchars($inv['return_rate'] ?? '0') ?>%
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-emerald-600">
                                <?= Formatter::currency((float)$inv['expected_return']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-36 bg-gray-200 rounded-full h-1.5 overflow-hidden mb-1">
                                    <div class="bg-[#C59B27] h-1.5 rounded-full" style="width: <?= min(100, max(5, (float)($inv['progress_percent'] ?? 0))) ?>%"></div>
                                </div>
                                <div class="text-[10px] text-[#6B7280]">
                                    <?= Formatter::date($inv['start_date']) ?> &rarr; <?= Formatter::date($inv['end_date']) ?>
                                    <span class="text-[#111827] font-medium">(<?= max(0, (int)($inv['days_remaining'] ?? 0)) ?>d left)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($inv['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
