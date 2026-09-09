<?php
/**
 * CapitalNest Nepal - 360-Degree User Banking Detail & Audit
 */
use App\Helpers\Formatter;

$pageTitle = 'Investor 360: ' . htmlspecialchars($targetUser['name']) . ' - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-6">
    <a href="/admin/users" class="text-xs font-semibold text-gray-500 hover:text-black inline-flex items-center space-x-1 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to Investor Directory</span>
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#111827] flex items-center space-x-3">
                <span><?= htmlspecialchars($targetUser['name']) ?></span>
                <span class="text-xs font-mono font-normal text-[#6B7280]">#<?= $targetUser['id'] ?></span>
            </h1>
            <div class="text-xs text-[#6B7280] mt-1 flex items-center space-x-3">
                <span>Email: <strong class="text-[#111827]"><?= htmlspecialchars($targetUser['email']) ?></strong></span>
                <span>&bull;</span>
                <span>Phone: <strong class="text-[#111827]"><?= htmlspecialchars($targetUser['phone'] ?? 'N/A') ?></strong></span>
                <span>&bull;</span>
                <span>Ref Code: <strong class="text-[#111827] font-mono"><?= htmlspecialchars($targetUser['referral_code']) ?></strong></span>
            </div>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-[11px] text-[#6B7280]">
                <div class="bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl px-3 py-2">
                    <div class="text-[#6B7280] uppercase tracking-[0.12em]">Registration IP</div>
                    <div class="mt-1 font-semibold text-[#111827] font-mono"><?= htmlspecialchars($targetUser['registration_ip'] ?? 'N/A') ?></div>
                </div>
                <div class="bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl px-3 py-2">
                    <div class="text-[#6B7280] uppercase tracking-[0.12em]">Last Seen IP</div>
                    <div class="mt-1 font-semibold text-[#111827] font-mono"><?= htmlspecialchars($targetUser['last_seen_ip'] ?? $targetUser['last_login_ip'] ?? 'N/A') ?></div>
                </div>
                <div class="bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl px-3 py-2">
                    <div class="text-[#6B7280] uppercase tracking-[0.12em]">Device Fingerprint</div>
                    <div class="mt-1 font-semibold text-[#111827] font-mono break-all"><?= htmlspecialchars($targetUser['device_fingerprint'] ?? 'N/A') ?></div>
                </div>
                <div class="bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl px-3 py-2">
                    <div class="text-[#6B7280] uppercase tracking-[0.12em]">VPN / Proxy</div>
                    <div class="mt-1 font-semibold <?= (!empty($targetUser['vpn_detected']) ? 'text-[#C59B27]' : 'text-emerald-600') ?>">
                        <?= !empty($targetUser['vpn_detected']) ? 'Detected' : 'Not Detected' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Status Toggle & Actions -->
        <div class="flex flex-col items-end space-y-3">
            <form action="/admin/users/status" method="POST" class="inline">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="user_id" value="<?= $targetUser['id'] ?>">
                <?php if ($targetUser['status'] === 'active'): ?>
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" onclick="return confirm('Suspend this account? User will be locked out of deposits and withdrawals.')"
                        class="px-4 py-2 bg-[#FEF9EE] border border-[#F3E8C6] hover:bg-[#FDF4DC] text-[#111827] rounded-xl text-xs font-bold transition">
                        Suspend Account
                    </button>
                <?php else: ?>
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="px-4 py-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-bold transition">
                        Restore to Active
                    </button>
                <?php endif; ?>
            </form>

            <form action="/admin/users/change-password" method="POST" class="w-full min-w-[280px] bg-white border border-[#E5E7EB] rounded-2xl p-4 shadow-sm">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="user_id" value="<?= $targetUser['id'] ?>">
                <div class="text-xs font-bold text-[#111827] uppercase tracking-wide mb-3">Reset User Password</div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-[#6B7280] mb-1">New Password</label>
                        <input type="password" name="new_password" minlength="8" required
                            class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-xs focus:outline-none focus:border-[#C59B27]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-[#6B7280] mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" minlength="8" required
                            class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-xs focus:outline-none focus:border-[#C59B27]">
                    </div>
                    <button type="submit" onclick="return confirm('Reset this user password?')" class="w-full px-4 py-2 bg-[#111827] hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition">
                        Update User Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Financial Summary (Wallet State) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Available Liquid Balance</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['available_balance']) ?></div>
        <span class="text-[11px] text-emerald-600 font-medium">Unencumbered liquid cash</span>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Invested Capital</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['invested_balance']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Locked in active tranches</span>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Lifetime Deposits</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['total_deposits']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Audited inflows</span>
    </div>

    <div class="p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
        <span class="text-xs font-medium text-[#6B7280]">Total Disbursed Withdrawn</span>
        <div class="mt-2 text-2xl font-bold text-[#111827]"><?= Formatter::currency((float)$wallet['total_withdrawals']) ?></div>
        <span class="text-[11px] text-[#6B7280]">Disbursed to bank/wallets</span>
    </div>
</div>

<!-- Tabs: KYC, Investments, Ledger, Deposits, Withdrawals, Referrals, Activity Logs -->
<div class="space-y-8">

    <!-- 1. KYC Compliance Record -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-[#111827] uppercase tracking-wider">KYC Compliance Review</h3>
                <p class="text-xs text-[#6B7280]">Civil identity verification status & submitted documents</p>
            </div>
            <div>
                <?= Formatter::kycBadge($kyc['status'] ?? 'not_submitted') ?>
            </div>
        </div>

        <?php if (!$kyc): ?>
            <div class="py-6 text-center text-xs text-[#6B7280]">Investor has not submitted KYC identity documents yet.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs bg-[#F8F9FA] p-4 rounded-xl border border-[#E5E7EB] mb-4">
                <div>
                    <span class="text-[#6B7280]">Document Type:</span>
                    <div class="font-bold text-[#111827]"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $kyc['document_type']))) ?></div>
                </div>
                <div>
                    <span class="text-[#6B7280]">ID Number:</span>
                    <div class="font-mono font-bold text-[#111827]"><?= htmlspecialchars($kyc['id_number']) ?></div>
                </div>
                <div>
                    <span class="text-[#6B7280]">Submitted Date:</span>
                    <div class="text-[#111827]"><?= Formatter::dateTime($kyc['submitted_at']) ?></div>
                </div>
                <div>
                    <span class="text-[#6B7280]">Full Legal Name:</span>
                    <div class="font-bold text-[#111827]"><?= htmlspecialchars($kyc['full_name']) ?></div>
                </div>
                <div>
                    <span class="text-[#6B7280]">Parents:</span>
                    <div class="text-[#111827]"><?= htmlspecialchars($kyc['father_name']) ?> / <?= htmlspecialchars($kyc['mother_name'] ?? '') ?></div>
                </div>
                <div>
                    <span class="text-[#6B7280]">Permanent Address:</span>
                    <div class="text-[#111827]"><?= htmlspecialchars($kyc['permanent_address']) ?></div>
                </div>
            </div>

            <!-- Documents View / Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <?php if (!empty($kyc['front_document_path'])): ?>
                    <a href="/secure-file?path=<?= urlencode($kyc['front_document_path']) ?>" target="_blank"
                        class="px-3 py-1.5 bg-white border border-[#E5E7EB] hover:border-[#C59B27] rounded-lg text-xs font-semibold text-[#111827] inline-flex items-center space-x-1.5 shadow-xs">
                        <i data-lucide="eye" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                        <span>View Front Document</span>
                    </a>
                <?php endif; ?>

                <?php if (!empty($kyc['back_document_path'])): ?>
                    <a href="/secure-file?path=<?= urlencode($kyc['back_document_path']) ?>" target="_blank"
                        class="px-3 py-1.5 bg-white border border-[#E5E7EB] hover:border-[#C59B27] rounded-lg text-xs font-semibold text-[#111827] inline-flex items-center space-x-1.5 shadow-xs">
                        <i data-lucide="eye" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                        <span>View Back Document</span>
                    </a>
                <?php endif; ?>

                <?php if (!empty($kyc['selfie_path'])): ?>
                    <a href="/secure-file?path=<?= urlencode($kyc['selfie_path']) ?>" target="_blank"
                        class="px-3 py-1.5 bg-white border border-[#E5E7EB] hover:border-[#C59B27] rounded-lg text-xs font-semibold text-[#111827] inline-flex items-center space-x-1.5 shadow-xs">
                        <i data-lucide="camera" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                        <span>View Selfie Verification</span>
                    </a>
                <?php endif; ?>

                <?php if ($kyc['status'] === 'pending'): ?>
                    <div class="ml-auto flex items-center space-x-2">
                        <form action="/admin/kyc/approve" method="POST" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="kyc_id" value="<?= $kyc['id'] ?>">
                            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                Approve KYC
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. Active Investment Holdings -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Investments Held</h3>
            <p class="text-xs text-[#6B7280]">All capital tranches allocated by this user.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="px-6 py-3">Plan / Ref</th>
                        <th class="px-6 py-3">Principal Amount</th>
                        <th class="px-6 py-3">Return Rate</th>
                        <th class="px-6 py-3">Maturity Date</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3F4F6]">
                    <?php if (empty($investments)): ?>
                        <tr><td colspan="5" class="px-6 py-6 text-center text-[#6B7280]">No investment tranches found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($investments as $inv): ?>
                            <tr>
                                <td class="px-6 py-3.5">
                                    <div class="font-bold text-[#111827]"><?= htmlspecialchars($inv['plan_name'] ?? 'Plan') ?></div>
                                    <div class="font-mono text-[10px] text-gray-400"><?= htmlspecialchars($inv['investment_ref']) ?></div>
                                </td>
                                <td class="px-6 py-3.5 font-mono font-bold"><?= Formatter::currency((float)$inv['amount']) ?></td>
                                <td class="px-6 py-3.5 font-bold text-[#C59B27]"><?= htmlspecialchars($inv['return_rate']) ?>%</td>
                                <td class="px-6 py-3.5"><?= Formatter::date($inv['end_date']) ?></td>
                                <td class="px-6 py-3.5"><?= Formatter::statusBadge($inv['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Recent Transactions Ledger -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Account Transaction Ledger</h3>
            <p class="text-xs text-[#6B7280]">Chronological audit of balance modifications.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="px-6 py-3">Ref</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Prior Balance</th>
                        <th class="px-6 py-3">Delta</th>
                        <th class="px-6 py-3">Closing Balance</th>
                        <th class="px-6 py-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3F4F6]">
                    <?php if (empty($txs)): ?>
                        <tr><td colspan="6" class="px-6 py-6 text-center text-[#6B7280]">No transactions recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($txs as $t): ?>
                            <tr>
                                <td class="px-6 py-3.5 font-mono"><?= htmlspecialchars($t['transaction_ref']) ?></td>
                                <td class="px-6 py-3.5"><?= Formatter::transactionTypeBadge($t['type']) ?></td>
                                <td class="px-6 py-3.5 font-mono"><?= Formatter::currency((float)$t['previous_balance']) ?></td>
                                <td class="px-6 py-3.5 font-mono font-bold <?= (float)$t['amount'] >= 0 ? 'text-emerald-600' : 'text-[#111827]' ?>">
                                    <?= (float)$t['amount'] >= 0 ? '+' : '' ?><?= Formatter::currency((float)$t['amount']) ?>
                                </td>
                                <td class="px-6 py-3.5 font-mono font-bold"><?= Formatter::currency((float)$t['new_balance']) ?></td>
                                <td class="px-6 py-3.5 text-[#6B7280]"><?= Formatter::dateTime($t['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
