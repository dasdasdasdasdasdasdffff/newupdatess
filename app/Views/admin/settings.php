<?php
/**
 * CapitalNest Nepal - Master Platform Settings & Payment Gateway Config
 */
use App\Helpers\Formatter;

$pageTitle = 'Platform Settings - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Platform Configuration & Treasury Gateways</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Control institutional financial policies, deposit limits, payment receiving accounts and maintenance states.</p>
</div>

<form action="/admin/settings/save" method="POST" enctype="multipart/form-data" class="space-y-8 max-w-4xl">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

    <!-- General Company Settings -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-[#111827] mb-1">Corporate Identity</h2>
        <p class="text-xs text-[#6B7280] mb-6">General entity naming and investor communication credentials.</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="company_name" class="block text-xs font-semibold text-[#111827] mb-1">Entity Name</label>
                <input id="company_name" name="company_name" type="text" required
                    value="<?= htmlspecialchars($settings['company_name'] ?? 'CapitalNest Nepal Pvt. Ltd.') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="support_email" class="block text-xs font-semibold text-[#111827] mb-1">Support Email</label>
                <input id="support_email" name="support_email" type="email" required
                    value="<?= htmlspecialchars($settings['support_email'] ?? 'support@capitalnest.np') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="support_phone" class="block text-xs font-semibold text-[#111827] mb-1">Support Phone</label>
                <input id="support_phone" name="support_phone" type="text"
                    value="<?= htmlspecialchars($settings['support_phone'] ?? '+977-01-4458921') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>
        </div>
    </div>

    <!-- Financial Policies & Thresholds -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-[#111827] mb-1">Financial Parameters</h2>
        <p class="text-xs text-[#6B7280] mb-6">Deposit and withdrawal constraints enforced at the database transaction layer.</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="min_deposit" class="block text-xs font-semibold text-[#111827] mb-1">Min Deposit (NPR)</label>
                <input id="min_deposit" name="min_deposit" type="number" step="10" required
                    value="<?= htmlspecialchars($settings['min_deposit'] ?? '500') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-bold focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="min_withdrawal" class="block text-xs font-semibold text-[#111827] mb-1">Min Withdrawal (NPR)</label>
                <input id="min_withdrawal" name="min_withdrawal" type="number" step="10" required
                    value="<?= htmlspecialchars($settings['min_withdrawal'] ?? '500') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-bold focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="referral_commission_rate" class="block text-xs font-semibold text-[#111827] mb-1">Direct Referral Rate (%)</label>
                <input id="referral_commission_rate" name="referral_commission_rate" type="number" step="0.1" required
                    value="<?= htmlspecialchars($settings['referral_commission_rate'] ?? '5.0') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-bold text-[#C59B27] focus:border-[#C59B27] focus:outline-none">
            </div>
        </div>
    </div>

    <!-- Official Receiving Accounts (Shown on User Deposit Page) -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-[#111827] mb-1">Official Receiving Accounts</h2>
        <p class="text-xs text-[#6B7280] mb-6">Bank and digital wallet accounts displayed to investors when submitting deposit slips.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="bank_name" class="block text-xs font-semibold text-[#111827] mb-1">Bank Name</label>
                <input id="bank_name" name="settings[bank_name]" type="text"
                    value="<?= htmlspecialchars($settings['bank_name'] ?? 'Nabil Bank Limited') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="bank_account_name" class="block text-xs font-semibold text-[#111827] mb-1">Account Holder Name</label>
                <input id="bank_account_name" name="settings[bank_account_name]" type="text"
                    value="<?= htmlspecialchars($settings['bank_account_name'] ?? 'CAPITALNEST NEPAL PVT LTD') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="bank_account_number" class="block text-xs font-semibold text-[#111827] mb-1">Account Number</label>
                <input id="bank_account_number" name="settings[bank_account_number]" type="text"
                    value="<?= htmlspecialchars($settings['bank_account_number'] ?? '01900175249821') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono font-bold focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="bank_branch" class="block text-xs font-semibold text-[#111827] mb-1">Branch Name</label>
                <input id="bank_branch" name="settings[bank_branch]" type="text"
                    value="<?= htmlspecialchars($settings['bank_branch'] ?? 'Kathmandu Corporate Branch') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="esewa_name" class="block text-xs font-semibold text-[#111827] mb-1">eSewa Display Name</label>
                <input id="esewa_name" name="settings[esewa_name]" type="text"
                    value="<?= htmlspecialchars($settings['esewa_name'] ?? 'CapitalNest Treasury') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="khalti_name" class="block text-xs font-semibold text-[#111827] mb-1">Khalti Display Name</label>
                <input id="khalti_name" name="settings[khalti_name]" type="text"
                    value="<?= htmlspecialchars($settings['khalti_name'] ?? 'CapitalNest Treasury') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="esewa_id" class="block text-xs font-semibold text-[#111827] mb-1">Official eSewa ID</label>
                <input id="esewa_id" name="settings[esewa_id]" type="text"
                    value="<?= htmlspecialchars($settings['esewa_id'] ?? '9801234567') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="khalti_id" class="block text-xs font-semibold text-[#111827] mb-1">Official Khalti ID</label>
                <input id="khalti_id" name="settings[khalti_id]" type="text"
                    value="<?= htmlspecialchars($settings['khalti_id'] ?? '9801234567') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="fonepay_name" class="block text-xs font-semibold text-[#111827] mb-1">FonePay Display Name</label>
                <input id="fonepay_name" name="settings[fonepay_name]" type="text"
                    value="<?= htmlspecialchars($settings['fonepay_name'] ?? 'CapitalNest Treasury') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="fonepay_id" class="block text-xs font-semibold text-[#111827] mb-1">Official FonePay ID (optional)</label>
                <input id="fonepay_id" name="settings[fonepay_id]" type="text"
                    value="<?= htmlspecialchars($settings['fonepay_id'] ?? '') ?>"
                    placeholder="Leave blank if only name is used"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono focus:border-[#C59B27] focus:outline-none">
            </div>

            <div>
                <label for="fonepay_qr_url" class="block text-xs font-semibold text-[#111827] mb-1">FonePay QR Image URL</label>
                <input id="fonepay_qr_url" name="settings[fonepay_qr_url]" type="url"
                    value="<?= htmlspecialchars($settings['fonepay_qr_url'] ?? '') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                <div class="mt-2">
                    <label for="fonepay_qr_upload" class="block text-[10px] font-semibold uppercase tracking-wide text-[#6B7280] mb-1">Upload FonePay QR</label>
                    <input id="fonepay_qr_upload" name="qr_uploads[fonepay_qr_url]" type="file" accept="image/*"
                        class="w-full text-[11px] text-[#6B7280] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#111827] file:text-white file:text-[10px] file:font-semibold">
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="esewa_qr_url" class="block text-xs font-semibold text-[#111827] mb-1">eSewa QR Image URL</label>
                <input id="esewa_qr_url" name="settings[esewa_qr_url]" type="url"
                    value="<?= htmlspecialchars($settings['esewa_qr_url'] ?? '') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                <div class="mt-2">
                    <label for="esewa_qr_upload" class="block text-[10px] font-semibold uppercase tracking-wide text-[#6B7280] mb-1">Upload eSewa QR</label>
                    <input id="esewa_qr_upload" name="qr_uploads[esewa_qr_url]" type="file" accept="image/*"
                        class="w-full text-[11px] text-[#6B7280] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#111827] file:text-white file:text-[10px] file:font-semibold">
                </div>
            </div>

            <div>
                <label for="khalti_qr_url" class="block text-xs font-semibold text-[#111827] mb-1">Khalti QR Image URL</label>
                <input id="khalti_qr_url" name="settings[khalti_qr_url]" type="url"
                    value="<?= htmlspecialchars($settings['khalti_qr_url'] ?? '') ?>"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                <div class="mt-2">
                    <label for="khalti_qr_upload" class="block text-[10px] font-semibold uppercase tracking-wide text-[#6B7280] mb-1">Upload Khalti QR</label>
                    <input id="khalti_qr_upload" name="qr_uploads[khalti_qr_url]" type="file" accept="image/*"
                        class="w-full text-[11px] text-[#6B7280] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#111827] file:text-white file:text-[10px] file:font-semibold">
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Mode Toggle -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-[#111827]">Maintenance Mode</h2>
                <p class="text-xs text-[#6B7280]">When enabled, non-admin users will see an operational maintenance notice.</p>
            </div>
            <div>
                <select name="maintenance_mode" class="px-3 py-2 bg-white border border-[#E5E7EB] rounded-xl text-xs font-bold focus:outline-none">
                    <option value="0" <?= (($settings['maintenance_mode'] ?? '0') === '0') ? 'selected' : '' ?>>Operational (Live)</option>
                    <option value="1" <?= (($settings['maintenance_mode'] ?? '0') === '1') ? 'selected' : '' ?>>Maintenance Active</option>
                </select>
            </div>
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="w-full py-3 px-4 bg-[#111827] hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-sm">
            <i data-lucide="check-circle" class="w-4 h-4 text-[#C59B27]"></i>
            <span>Commit Platform Configuration Changes</span>
        </button>
    </div>
</form>

<div class="mt-8 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8 max-w-2xl">
    <h2 class="text-base font-bold text-[#111827] mb-1">Change Administrator Password</h2>
    <p class="text-xs text-[#6B7280] mb-6">Use this to update your own admin login password securely.</p>

    <form action="/admin/change-password" method="POST" class="space-y-4">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div>
            <label for="current_password" class="block text-xs font-semibold text-[#111827] mb-1">Current Password</label>
            <input id="current_password" name="current_password" type="password" required
                class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
        </div>

        <div>
            <label for="new_password" class="block text-xs font-semibold text-[#111827] mb-1">New Password</label>
            <input id="new_password" name="new_password" type="password" minlength="8" required
                class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
        </div>

        <div>
            <label for="confirm_password" class="block text-xs font-semibold text-[#111827] mb-1">Confirm New Password</label>
            <input id="confirm_password" name="confirm_password" type="password" minlength="8" required
                class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-[#111827] hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition">
            Update My Admin Password
        </button>
    </form>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
