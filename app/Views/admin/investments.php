<?php
/**
 * CapitalNest Nepal - Admin Investment Plans & Active Tranches
 */
use App\Helpers\Formatter;

$pageTitle = 'Investment Management - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Investment Plans & Holdings</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Manage commercial treasury offerings and audit live investor tranches.</p>
    </div>
    <div>
        <button onclick="togglePlanModal(true)" class="px-4 py-2 bg-[#111827] hover:bg-gray-800 text-white rounded-xl text-xs font-semibold shadow-sm transition inline-flex items-center space-x-1.5">
            <i data-lucide="plus" class="w-4 h-4 text-[#C59B27]"></i>
            <span>Create New Plan</span>
        </button>
    </div>
</div>

<!-- Plan Offerings Section -->
<div class="mb-10">
    <div class="text-xs font-bold uppercase tracking-wider text-[#6B7280] mb-4">Configured Investment Instruments</div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($plans as $p): ?>
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $p['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= htmlspecialchars($p['status']) ?>
                        </span>
                        <span class="text-xs font-extrabold text-[#C59B27] bg-[#FEF9EE] px-2 py-0.5 rounded-full border border-[#F3E8C6]">
                            +<?= htmlspecialchars($p['return_rate']) ?>%
                        </span>
                    </div>

                    <h3 class="font-bold text-sm text-[#111827] mb-1"><?= htmlspecialchars($p['name']) ?></h3>
                    <p class="text-xs text-[#6B7280] mb-4 line-clamp-2"><?= htmlspecialchars($p['description'] ?? '') ?></p>

                    <div class="space-y-1.5 text-xs border-t border-[#F3F4F6] pt-3 text-[#6B7280]">
                        <div class="flex justify-between">
                            <span>Tenure:</span>
                            <span class="font-bold text-[#111827]"><?= (int)$p['duration_days'] ?> Days</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Min Entry:</span>
                            <span class="font-bold text-[#111827]"><?= Formatter::currency((float)$p['min_investment']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Max Cap:</span>
                            <span class="font-bold text-[#111827]"><?= Formatter::currency((float)$p['max_investment']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-[#F3F4F6] flex justify-end space-x-2">
                    <button type="button" onclick="editPlan(<?= htmlspecialchars(json_encode($p)) ?>)" class="text-xs font-semibold text-[#111827] hover:underline">Edit</button>
                    <span>&bull;</span>
                    <form action="/admin/investments/plans/toggle" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="text-xs font-semibold <?= $p['status'] === 'active' ? 'text-amber-600' : 'text-emerald-600' ?> hover:underline">
                            <?= $p['status'] === 'active' ? 'Disable' : 'Enable' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Active User Investments Table -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-[#E5E7EB] flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-[#111827]">Investor Portfolio Allocations</h2>
            <p class="text-xs text-[#6B7280]">Audited capital tranches active in market plans.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Investor</th>
                    <th class="px-6 py-3.5">Plan / Ref</th>
                    <th class="px-6 py-3.5">Principal Invested</th>
                    <th class="px-6 py-3.5">Expected Return</th>
                    <th class="px-6 py-3.5">Dates / Tenure</th>
                    <th class="px-6 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($investments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-[#6B7280]">
                            No investor holdings recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($investments as $inv): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $inv['user_id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($inv['user_name'] ?? 'Investor') ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($inv['user_email'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($inv['plan_name']) ?></div>
                                <div class="text-[10px] font-mono text-[#9CA3AF]"><?= htmlspecialchars($inv['investment_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-sm text-[#111827]">
                                <?= Formatter::currency((float)$inv['amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-emerald-600">
                                <?= Formatter::currency((float)$inv['expected_return']) ?>
                                <span class="text-[10px] text-[#C59B27] font-semibold">(+<?= htmlspecialchars($inv['return_rate']) ?>%)</span>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <div><?= Formatter::date($inv['start_date']) ?> &rarr; <?= Formatter::date($inv['end_date']) ?></div>
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

<!-- Modal: Create / Edit Plan -->
<div id="planModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-[#E5E7EB] shadow-2xl p-6">
        <div class="flex justify-between items-center pb-3 border-b border-[#E5E7EB]">
            <h3 id="planModalTitle" class="text-base font-bold text-[#111827]">Configure Investment Plan</h3>
            <button onclick="togglePlanModal(false)" class="text-gray-400 hover:text-gray-600 p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="/admin/investments/plans/save" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="plan_id" id="planFormId" value="">

            <div>
                <label for="pName" class="block text-xs font-semibold text-[#111827] mb-1">Plan Name</label>
                <input id="pName" name="name" type="text" required class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none" placeholder="e.g. 90-Day Treasury Note">
            </div>

            <div>
                <label for="pDesc" class="block text-xs font-semibold text-[#111827] mb-1">Description</label>
                <textarea id="pDesc" name="description" rows="2" class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none" placeholder="Underlying collateral and risk profile..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="pRate" class="block text-xs font-semibold text-[#111827] mb-1">Return Rate (%)</label>
                    <input id="pRate" name="return_rate" type="number" step="0.1" required class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-bold focus:border-[#C59B27] focus:outline-none" placeholder="12.5">
                </div>
                <div>
                    <label for="pDays" class="block text-xs font-semibold text-[#111827] mb-1">Duration (Days)</label>
                    <input id="pDays" name="duration_days" type="number" required class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-bold focus:border-[#C59B27] focus:outline-none" placeholder="60">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="pMin" class="block text-xs font-semibold text-[#111827] mb-1">Min Investment (NPR)</label>
                    <input id="pMin" name="min_investment" type="number" step="100" required class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono focus:border-[#C59B27] focus:outline-none" placeholder="10000">
                </div>
                <div>
                    <label for="pMax" class="block text-xs font-semibold text-[#111827] mb-1">Max Investment (NPR)</label>
                    <input id="pMax" name="max_investment" type="number" step="100" required class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono focus:border-[#C59B27] focus:outline-none" placeholder="500000">
                </div>
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="togglePlanModal(false)" class="flex-1 py-2 px-4 bg-gray-100 text-[#111827] rounded-xl text-xs font-semibold hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="flex-1 py-2 px-4 bg-[#111827] text-white rounded-xl text-xs font-bold hover:bg-gray-800 transition">Save Instrument</button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePlanModal(show) {
        if (show) {
            document.getElementById('planFormId').value = '';
            document.getElementById('pName').value = '';
            document.getElementById('pDesc').value = '';
            document.getElementById('pRate').value = '';
            document.getElementById('pDays').value = '';
            document.getElementById('pMin').value = '10000';
            document.getElementById('pMax').value = '500000';
            document.getElementById('planModalTitle').innerText = 'Create Investment Plan';
        }
        document.getElementById('planModal').classList.toggle('hidden', !show);
    }

    function editPlan(p) {
        document.getElementById('planFormId').value = p.id;
        document.getElementById('pName').value = p.name;
        document.getElementById('pDesc').value = p.description || '';
        document.getElementById('pRate').value = p.return_rate;
        document.getElementById('pDays').value = p.duration_days;
        document.getElementById('pMin').value = p.min_investment;
        document.getElementById('pMax').value = p.max_investment;
        document.getElementById('planModalTitle').innerText = 'Edit Plan: ' + p.name;
        document.getElementById('planModal').classList.remove('hidden');
    }
</script>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
