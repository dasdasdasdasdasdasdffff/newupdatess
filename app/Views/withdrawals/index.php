<?php
/**
 * CapitalNest Nepal - Capital Disbursement & Withdrawal Gateway
 */
use App\Helpers\Formatter;

$pageTitle = 'Withdraw Funds - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Withdraw Funds</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Disburse available profits and liquid capital to your designated Nepalese bank account or digital wallet.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">

    <!-- Withdrawal Form -->
    <div class="lg:col-span-2 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-[#111827] mb-1">Request Capital Disbursement</h2>
        <p class="text-xs text-[#6B7280] mb-6">Funds are held in escrow reservation immediately upon request submission.</p>

        <?php if ($kycStatus !== 'verified'): ?>
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <div class="font-bold mb-1">KYC verification required for withdrawals</div>
                <div>Please complete and get your KYC approved before requesting a withdrawal.</div>
            </div>
        <?php endif; ?>

        <form action="/withdrawals/submit" method="POST" class="space-y-5" <?php if ($kycStatus !== 'verified') echo 'onsubmit="return false;"'; ?>>
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <!-- Available Balance Notice -->
            <div class="p-4 bg-[#FEF9EE] border border-[#F3E8C6] rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-xs text-[#6B7280]">Available for Withdrawal:</span>
                    <div class="text-lg font-extrabold text-[#111827]"><?= Formatter::currency((float)$wallet['available_balance']) ?></div>
                </div>
                <button type="button" onclick="setWithdrawAll(<?= (float)$wallet['available_balance'] ?>)" class="text-xs font-bold text-[#C59B27] hover:underline">
                    Withdraw Entire Balance
                </button>
            </div>

            <!-- Amount Input -->
            <div>
                <label for="wAmount" class="block text-xs font-semibold text-[#111827] mb-1">Withdrawal Amount (NPR)</label>
                <input id="wAmount" name="amount" type="number" step="100" min="500" max="<?= (float)$wallet['available_balance'] ?>" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm font-bold text-[#111827] placeholder-[#9CA3AF] focus:border-[#C59B27] focus:outline-none"
                    placeholder="Min. 500 NPR">
                <p class="text-[11px] text-[#6B7280] mt-1">Minimum payout is NPR 500. Fixed disbursement processing fee: NPR 0.00.</p>
            </div>

            <!-- Withdrawal Method -->
            <div>
                <label class="block text-xs font-semibold text-[#111827] mb-2">Disbursement Channel</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="border border-[#E5E7EB] p-3 rounded-xl cursor-pointer hover:border-[#C59B27] flex flex-col items-center text-center transition">
                        <input type="radio" name="method" value="Bank Transfer" checked class="sr-only" onchange="toggleBankFields(true)">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs mb-1">
                            <i data-lucide="landmark" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-[#111827]">Bank A/C</span>
                    </label>

                    <label class="border border-[#E5E7EB] p-3 rounded-xl cursor-pointer hover:border-[#C59B27] flex flex-col items-center text-center transition">
                        <input type="radio" name="method" value="eSewa" class="sr-only" onchange="toggleBankFields(false)">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs mb-1">eS</div>
                        <span class="text-xs font-bold text-[#111827]">eSewa</span>
                    </label>

                    <label class="border border-[#E5E7EB] p-3 rounded-xl cursor-pointer hover:border-[#C59B27] flex flex-col items-center text-center transition">
                        <input type="radio" name="method" value="Khalti" class="sr-only" onchange="toggleBankFields(false)">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs mb-1">Kh</div>
                        <span class="text-xs font-bold text-[#111827]">Khalti</span>
                    </label>
                </div>
            </div>

            <!-- Beneficiary Account Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="account_name" class="block text-xs font-semibold text-[#111827] mb-1">Beneficiary Account Name</label>
                    <input id="account_name" name="account_name" type="text" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm text-[#111827] focus:border-[#C59B27] focus:outline-none"
                        placeholder="Must match your KYC name">
                </div>

                <div>
                    <label for="account_number" class="block text-xs font-semibold text-[#111827] mb-1">Account Number / Wallet ID</label>
                    <input id="account_number" name="account_number" type="text" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm font-mono text-[#111827] focus:border-[#C59B27] focus:outline-none"
                        placeholder="Bank A/C or 98XXXXXXXX">
                </div>
            </div>

            <!-- Bank Specific Inputs (Toggled) -->
            <div id="bankFields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="bank_name" class="block text-xs font-semibold text-[#111827] mb-1">Bank Name</label>
                    <input id="bank_name" name="bank_name" type="text"
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm text-[#111827] focus:border-[#C59B27] focus:outline-none"
                        placeholder="e.g. Nabil Bank, Global IME">
                </div>
                <div>
                    <label for="branch_name" class="block text-xs font-semibold text-[#111827] mb-1">Branch Name</label>
                    <input id="branch_name" name="branch_name" type="text"
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm text-[#111827] focus:border-[#C59B27] focus:outline-none"
                        placeholder="e.g. New Road / Pokhara">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full py-3 px-4 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-sm" <?php if ($kycStatus !== 'verified') echo 'disabled aria-disabled="true"'; ?>>
                    <i data-lucide="arrow-up-circle" class="w-4 h-4 text-[#C59B27]"></i>
                    <span><?= $kycStatus === 'verified' ? 'Confirm & Lock Payout Request' : 'KYC Verification Required' ?></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Payout Security Overview -->
    <div class="space-y-6">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
            <div class="text-xs font-bold uppercase tracking-wider text-[#6B7280] mb-3">Disbursement Schedule</div>
            <ul class="space-y-2.5 text-xs text-[#6B7280]">
                <li class="flex items-start space-x-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                    <span>Disbursements run Monday through Friday at 11:30 AM and 4:00 PM NPT.</span>
                </li>
                <li class="flex items-start space-x-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                    <span>IPS and digital wallet transfers settle within 2 hours of approval.</span>
                </li>
                <li class="flex items-start space-x-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                    <span>If rejected for invalid account numbers, reserved funds are automatically refunded to your available balance.</span>
                </li>
            </ul>
        </div>
    </div>

</div>

<!-- User Withdrawal History -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Your Withdrawal Ledger</h2>
        <p class="text-xs text-[#6B7280]">Real-time tracking of queued, processing and disbursed payouts.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Method</th>
                    <th class="px-6 py-3.5">Amount</th>
                    <th class="px-6 py-3.5">Beneficiary Account</th>
                    <th class="px-6 py-3.5">Requested At</th>
                    <th class="px-6 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#6B7280]">
                            No withdrawal requests recorded.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($withdrawals as $w): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($w['method']) ?></div>
                                <div class="text-[10px] text-[#9CA3AF] font-mono"><?= htmlspecialchars($w['withdrawal_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-[#111827]">
                                <?= Formatter::currency((float)$w['amount']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($w['account_name']) ?></div>
                                <div class="text-[10px] text-[#6B7280] font-mono"><?= htmlspecialchars($w['account_number']) ?> <?= !empty($w['bank_name']) ? '(' . htmlspecialchars($w['bank_name']) . ')' : '' ?></div>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($w['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($w['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function setWithdrawAll(max) {
        document.getElementById('wAmount').value = max;
    }
    function toggleBankFields(show) {
        const f = document.getElementById('bankFields');
        if (show) {
            f.classList.remove('hidden');
        } else {
            f.classList.add('hidden');
        }
    }
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
