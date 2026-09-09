<?php
/**
 * CapitalNest Nepal - Deposit Request & Treasury Gate
 */
use App\Helpers\Formatter;

$pageTitle = 'Deposit Funds - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Deposit Funds</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Top up your investment wallet via official Nepalese banking channels or digital wallets.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">

    <!-- Deposit Form -->
    <div class="lg:col-span-3 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-[#111827] mb-1">New Deposit Request</h2>
        <p class="text-xs text-[#6B7280] mb-6">Choose the wallet you are paying from, complete the transfer, and submit your reference details.</p>

        <form action="/deposits/submit" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <!-- Payment Method Selector -->
            <div>
                <label class="block text-xs font-semibold text-[#111827] mb-2">Select Payment Method</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="border border-[#E5E7EB] p-3 rounded-xl cursor-pointer hover:border-[#C59B27] flex flex-col items-center text-center transition method-card">
                        <input type="radio" name="payment_method" value="eSewa" checked class="sr-only" onchange="updateMethodNotice(this.value)">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs mb-1.5">eS</div>
                        <span class="text-xs font-bold text-[#111827]">eSewa</span>
                    </label>

                    <label class="border border-[#E5E7EB] p-3 rounded-xl cursor-pointer hover:border-[#C59B27] flex flex-col items-center text-center transition method-card">
                        <input type="radio" name="payment_method" value="Khalti" class="sr-only" onchange="updateMethodNotice(this.value)">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs mb-1.5">Kh</div>
                        <span class="text-xs font-bold text-[#111827]">Khalti</span>
                    </label>

                    <label class="border border-[#E5E7EB] p-3 rounded-xl cursor-pointer hover:border-[#C59B27] flex flex-col items-center text-center transition method-card">
                        <input type="radio" name="payment_method" value="FonePay" class="sr-only" onchange="updateMethodNotice(this.value)">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs mb-1.5">FP</div>
                        <span class="text-xs font-bold text-[#111827]">FonePay</span>
                    </label>
                </div>
            </div>

            <?php
                $esewaName = htmlspecialchars($settings['esewa_name'] ?? 'CapitalNest Treasury');
                $khaltiName = htmlspecialchars($settings['khalti_name'] ?? 'CapitalNest Treasury');
                $fonepayName = htmlspecialchars($settings['fonepay_name'] ?? 'CapitalNest Treasury');
                $esewaId = htmlspecialchars($settings['esewa_id'] ?? '9801234567');
                $khaltiId = htmlspecialchars($settings['khalti_id'] ?? '9801234567');
                $fonepayId = htmlspecialchars($settings['fonepay_id'] ?? '');
                $esewaQr = (string)($settings['esewa_qr_url'] ?? '');
                $khaltiQr = (string)($settings['khalti_qr_url'] ?? '');
                $fonepayQr = (string)($settings['fonepay_qr_url'] ?? '');

                $resolveQrSource = function (string $value): string {
                    $trimmed = trim($value);
                    if ($trimmed === '') {
                        return '';
                    }
                    if (preg_match('#^https?://#i', $trimmed) || str_starts_with($trimmed, '/')) {
                        return $trimmed;
                    }
                    return '/secure-file?path=' . rawurlencode($trimmed);
                };

                $esewaQrSrc = htmlspecialchars($resolveQrSource($esewaQr));
                $khaltiQrSrc = htmlspecialchars($resolveQrSource($khaltiQr));
                $fonepayQrSrc = htmlspecialchars($resolveQrSource($fonepayQr));
            ?>

            <div class="space-y-3 text-xs">
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-[#6B7280]">
                    <i data-lucide="building" class="w-4 h-4 text-[#C59B27]"></i>
                    <span>Official Receiving Accounts</span>
                </div>

                <div class="method-detail rounded-xl border border-[#E5E7EB] bg-[#F8F9FA] p-3.5 shadow-sm" data-method="eSewa">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="font-bold text-[#111827]">eSewa</div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center font-bold text-[10px]">ES</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto] gap-3 items-center">
                        <div class="text-[#6B7280] font-mono text-[11px] leading-relaxed">
                            <div>ID: <span class="font-bold text-[#111827]"><?= $esewaId ?></span></div>
                            <div>Name: <span class="font-bold text-[#111827]"><?= $esewaName ?></span></div>
                        </div>
                        <div class="w-64 h-64 md:w-60 md:h-60 rounded-[26px] border-[7px] border-[#A7B0BA] bg-white flex items-center justify-center overflow-hidden shadow-sm">
                            <?php if (!empty($esewaQr)): ?>
                                <img src="<?= $esewaQrSrc ?>" alt="eSewa QR code" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-[16px] font-bold text-[#6B7280] text-center leading-tight">QR</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="method-detail rounded-xl border border-[#E5E7EB] bg-[#F8F9FA] p-3.5 shadow-sm hidden" data-method="Khalti">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="font-bold text-[#111827]">Khalti</div>
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center font-bold text-[10px]">KH</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto] gap-3 items-center">
                        <div class="text-[#6B7280] font-mono text-[11px] leading-relaxed">
                            <div>ID: <span class="font-bold text-[#111827]"><?= $khaltiId ?></span></div>
                            <div>Name: <span class="font-bold text-[#111827]"><?= $khaltiName ?></span></div>
                        </div>
                        <div class="w-64 h-64 md:w-60 md:h-60 rounded-[26px] border-[7px] border-[#A7B0BA] bg-white flex items-center justify-center overflow-hidden shadow-sm">
                            <?php if (!empty($khaltiQr)): ?>
                                <img src="<?= $khaltiQrSrc ?>" alt="Khalti QR code" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-[16px] font-bold text-[#6B7280] text-center leading-tight">QR</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="method-detail rounded-xl border border-[#E5E7EB] bg-[#F8F9FA] p-3.5 shadow-sm hidden" data-method="FonePay">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="font-bold text-[#111827]">FonePay</div>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center font-bold text-[10px]">FO</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto] gap-3 items-center">
                        <div class="text-[#6B7280] font-mono text-[11px] leading-relaxed">
                            <?php if (!empty($fonepayId)): ?>
                                <div>ID: <span class="font-bold text-[#111827]"><?= $fonepayId ?></span></div>
                            <?php endif; ?>
                            <div>Name: <span class="font-bold text-[#111827]"><?= $fonepayName ?></span></div>
                        </div>
                        <div class="w-64 h-64 md:w-60 md:h-60 rounded-[26px] border-[7px] border-[#A7B0BA] bg-white flex items-center justify-center overflow-hidden shadow-sm">
                            <?php if (!empty($fonepayQr)): ?>
                                <img src="<?= $fonepayQrSrc ?>" alt="FonePay QR code" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-[16px] font-bold text-[#6B7280] text-center leading-tight">QR</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Input -->
            <div class="pt-1">
                <label for="amount" class="block text-xs font-semibold text-[#111827] mb-1">Deposit Amount (NPR)</label>
                <div class="relative">
                    <input id="amount" name="amount" type="number" step="100" min="500" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm font-bold text-[#111827] placeholder-[#9CA3AF] focus:border-[#C59B27] focus:outline-none"
                        placeholder="Min. 500 NPR">
                </div>
                <p class="text-[11px] text-[#6B7280] mt-1">Minimum deposit is NPR 500. Zero platform processing fees applied.</p>
            </div>

            <!-- Transaction Reference Number -->
            <div>
                <label for="reference_number" class="block text-xs font-semibold text-[#111827] mb-1">Transaction Ref / UTR / Voucher ID</label>
                <input id="reference_number" name="reference_number" type="text" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-sm font-mono text-[#111827] placeholder-[#9CA3AF] focus:border-[#C59B27] focus:outline-none uppercase"
                    placeholder="e.g. ES982479213 / NBL998242">
                <p class="text-[11px] text-[#6B7280] mt-1">Found on your bank SMS, statement or eSewa/Khalti/FonePay receipt.</p>
            </div>

            <!-- Proof of Payment Upload -->
            <div>
                <label for="proof_file" class="block text-xs font-semibold text-[#111827] mb-1">Upload Receipt / Screenshot (Optional)</label>
                <input id="proof_file" name="proof_file" type="file" accept="image/jpeg,image/png,image/webp,application/pdf"
                    class="w-full text-xs text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#111827] file:text-white hover:file:bg-[#1F2937]">
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full py-3 px-4 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#C59B27]"></i>
                    <span>Submit Deposit for Treasury Clearance</span>
                </button>
            </div>
        </form>
    </div>

</div>

<!-- User Deposit History -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-[#E5E7EB]">
        <h2 class="text-base font-bold text-[#111827]">Your Deposit Inflows</h2>
        <p class="text-xs text-[#6B7280]">Real-time status of all submitted deposit vouchers.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Method</th>
                    <th class="px-6 py-3.5">Amount</th>
                    <th class="px-6 py-3.5">Transaction Ref</th>
                    <th class="px-6 py-3.5">Submitted</th>
                    <th class="px-6 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($deposits)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#6B7280]">
                            No deposit requests submitted yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deposits as $dep): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#111827]"><?= htmlspecialchars($dep['payment_method']) ?></div>
                                <div class="text-[10px] text-[#9CA3AF] font-mono"><?= htmlspecialchars($dep['deposit_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-[#111827]">
                                <?= Formatter::currency((float)$dep['amount']) ?>
                            </td>
                            <td class="px-6 py-4 font-mono text-[#6B7280]">
                                <?= htmlspecialchars($dep['reference_number'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($dep['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($dep['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function updateMethodNotice(val) {
        const cards = document.querySelectorAll('.method-card');
        const details = document.querySelectorAll('.method-detail');

        cards.forEach((card) => {
            const input = card.querySelector('input[name="payment_method"]');
            const active = input && input.value === val;
            card.classList.toggle('border-[#C59B27]', active);
            card.classList.toggle('bg-[#F9F6EE]', active);
            card.classList.toggle('shadow-sm', active);
        });

        details.forEach((detail) => {
            const isVisible = detail.dataset.method === val;
            detail.classList.toggle('hidden', !isVisible);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (selected) {
            updateMethodNotice(selected.value);
        }
    });
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
