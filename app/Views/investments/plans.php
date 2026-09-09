<?php
/**
 * CapitalNest Nepal - Investment Plans Catalog & Return Simulator
 */
use App\Helpers\Formatter;
use App\Helpers\Security;

$pageTitle = 'Investment Plans - CapitalNest Nepal';
$csrf = Security::generateCsrfToken();
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Vetted Investment Plans</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Commercial treasury bonds, hydro infrastructure equity, and secured fixed return instruments.</p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="/investments/my" class="px-4 py-2 bg-white hover:bg-gray-50 border border-[#E5E7EB] text-[#111827] rounded-xl text-xs font-semibold shadow-sm transition inline-flex items-center space-x-1.5">
            <i data-lucide="pie-chart" class="w-4 h-4 text-[#C59B27]"></i>
            <span>View My Active Portfolio</span>
        </a>
    </div>
</div>

<!-- Live Balance Banner -->
<div class="p-4 bg-[#FEF9EE] border border-[#F3E8C6] rounded-2xl mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-white border border-[#F3E8C6] flex items-center justify-center text-[#C59B27] shadow-xs">
            <i data-lucide="wallet" class="w-5 h-5"></i>
        </div>
        <div>
            <span class="text-xs text-[#6B7280]">Your Liquid Available Balance:</span>
            <div class="text-lg font-extrabold text-[#111827]"><?= Formatter::currency((float)$wallet['available_balance']) ?></div>
        </div>
    </div>
    <div class="flex items-center space-x-2">
        <a href="/deposits" class="px-3.5 py-1.5 bg-[#111827] text-white text-xs font-semibold rounded-xl hover:bg-[#1F2937] transition">Top Up Wallet</a>
    </div>
</div>

<!-- Plans Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <?php foreach ($plans as $plan): ?>
        <div class="bg-white border border-[#E5E7EB] hover:border-[#C59B27] rounded-2xl shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between relative group">
            
            <!-- Plan Badge -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-gray-100 text-[#111827]">
                        <?= htmlspecialchars(ucfirst($plan['payout_frequency'])) ?> Yield
                    </span>
                    <span class="text-xs font-extrabold text-[#C59B27] bg-[#FEF9EE] border border-[#F3E8C6] px-2.5 py-0.5 rounded-full">
                        +<?= htmlspecialchars($plan['return_rate']) ?>%
                    </span>
                </div>

                <h3 class="text-base font-bold text-[#111827] mb-1"><?= htmlspecialchars($plan['name']) ?></h3>
                <p class="text-xs text-[#6B7280] leading-relaxed mb-4"><?= htmlspecialchars($plan['description'] ?? '') ?></p>

                <div class="space-y-2.5 py-3 border-t border-b border-[#F3F4F6] text-xs">
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Tenure:</span>
                        <span class="font-bold text-[#111827]"><?= (int)$plan['duration_days'] ?> Days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Min Investment:</span>
                        <span class="font-bold text-[#111827]"><?= Formatter::currency((float)$plan['min_investment']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Max Allocation:</span>
                        <span class="font-bold text-[#111827]"><?= Formatter::currency((float)$plan['max_investment']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Principal Return:</span>
                        <span class="font-bold text-emerald-600">100% Guaranteed</span>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-6">
                <button type="button" 
                    onclick="openInvestModal(<?= htmlspecialchars(json_encode($plan)) ?>)"
                    class="w-full py-2.5 px-4 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2">
                    <span>Invest in Tranche</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Return Simulator Tool -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8 max-w-3xl mx-auto">
    <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-[#111827]">Interactive Return Simulator</h2>
        <p class="text-xs text-[#6B7280]">Calculate forecasted principal return and net capital appreciation.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-[#111827] mb-1">Select Investment Plan</label>
            <select id="simPlan" onchange="runSimulation()" class="w-full px-3 py-2 text-xs bg-white border border-[#E5E7EB] rounded-xl focus:border-[#C59B27] focus:outline-none">
                <?php foreach ($plans as $p): ?>
                    <option value="<?= $p['id'] ?>" data-rate="<?= $p['return_rate'] ?>" data-days="<?= $p['duration_days'] ?>" data-min="<?= $p['min_investment'] ?>" data-max="<?= $p['max_investment'] ?>">
                        <?= htmlspecialchars($p['name']) ?> (<?= $p['return_rate'] ?>% / <?= $p['duration_days'] ?> Days)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-[#111827] mb-1">Investment Amount (NPR)</label>
            <input type="number" id="simAmount" value="50000" min="1000" step="1000" oninput="runSimulation()" class="w-full px-3 py-2 text-xs bg-white border border-[#E5E7EB] rounded-xl focus:border-[#C59B27] focus:outline-none font-bold">
        </div>
    </div>

    <!-- Projected Simulation Results -->
    <div class="p-4 bg-[#F8F9FA] rounded-xl border border-[#E5E7EB] grid grid-cols-3 gap-3 text-center">
        <div>
            <div class="text-[10px] uppercase text-[#6B7280] font-semibold">Invested Principal</div>
            <div id="simPrincipalDisplay" class="text-sm font-bold text-[#111827] mt-0.5">NPR 50,000</div>
        </div>
        <div>
            <div class="text-[10px] uppercase text-[#6B7280] font-semibold">Net Profit</div>
            <div id="simProfitDisplay" class="text-sm font-bold text-[#C59B27] mt-0.5">NPR 5,000</div>
        </div>
        <div>
            <div class="text-[10px] uppercase text-[#6B7280] font-semibold">Total At Maturity</div>
            <div id="simTotalDisplay" class="text-sm font-bold text-emerald-600 mt-0.5">NPR 55,000</div>
        </div>
    </div>
</div>

<!-- Modal: Execute Investment -->
<div id="investModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-[#E5E7EB] shadow-2xl p-6">
        <div class="flex justify-between items-center pb-3 border-b border-[#E5E7EB]">
            <div>
                <h3 id="modalPlanName" class="text-base font-bold text-[#111827]">Confirm Investment</h3>
                <p class="text-[11px] text-[#6B7280]">Funds will be deducted from your liquid wallet.</p>
            </div>
            <button onclick="closeInvestModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="/investments/invest" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="plan_id" id="modalPlanId" value="">

            <div class="p-3 bg-[#FEF9EE] border border-[#F3E8C6] rounded-xl text-xs space-y-1">
                <div class="flex justify-between text-[#6B7280]">
                    <span>Return Rate:</span>
                    <span id="modalPlanRate" class="font-bold text-[#C59B27]">10.0%</span>
                </div>
                <div class="flex justify-between text-[#6B7280]">
                    <span>Duration:</span>
                    <span id="modalPlanDays" class="font-bold text-[#111827]">30 Days</span>
                </div>
                <div class="flex justify-between text-[#6B7280]">
                    <span>Limits:</span>
                    <span id="modalPlanLimits" class="font-mono text-[#111827]">NPR 5,000 - NPR 500,000</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#111827] mb-1">Amount to Invest (NPR)</label>
                <input type="number" name="amount" id="modalAmount" required step="100" 
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm font-bold focus:border-[#C59B27] focus:outline-none"
                    placeholder="Enter amount...">
                <div class="flex justify-between text-[11px] text-[#6B7280] mt-1">
                    <span>Available: <?= Formatter::currency((float)$wallet['available_balance']) ?></span>
                    <button type="button" onclick="setModalMax(<?= (float)$wallet['available_balance'] ?>)" class="text-[#C59B27] font-semibold hover:underline">Use Max Available</button>
                </div>
            </div>

            <div class="pt-2 flex space-x-3">
                <button type="button" onclick="closeInvestModal()" class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-[#111827] rounded-xl text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 px-4 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1">
                    <span>Confirm & Activate</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function runSimulation() {
        const sel = document.getElementById('simPlan');
        const opt = sel.options[sel.selectedIndex];
        const rate = parseFloat(opt.getAttribute('data-rate')) || 0;
        const amount = parseFloat(document.getElementById('simAmount').value) || 0;
        const profit = amount * (rate / 100);
        const total = amount + profit;

        document.getElementById('simPrincipalDisplay').innerText = 'NPR ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('simProfitDisplay').innerText = 'NPR ' + profit.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('simTotalDisplay').innerText = 'NPR ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    function openInvestModal(plan) {
        document.getElementById('modalPlanName').innerText = plan.name;
        document.getElementById('modalPlanId').value = plan.id;
        document.getElementById('modalPlanRate').innerText = plan.return_rate + '%';
        document.getElementById('modalPlanDays').innerText = plan.duration_days + ' Days';
        document.getElementById('modalPlanLimits').innerText = 'NPR ' + Number(plan.min_investment).toLocaleString() + ' - NPR ' + Number(plan.max_investment).toLocaleString();
        document.getElementById('modalAmount').min = plan.min_investment;
        document.getElementById('modalAmount').max = plan.max_investment;
        document.getElementById('modalAmount').value = plan.min_investment;
        document.getElementById('investModal').classList.remove('hidden');
    }

    function closeInvestModal() {
        document.getElementById('investModal').classList.add('hidden');
    }

    function setModalMax(available) {
        document.getElementById('modalAmount').value = available;
    }

    // Run initial simulator
    runSimulation();
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
