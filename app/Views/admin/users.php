<?php
/**
 * CapitalNest Nepal - Admin Users Directory
 */
use App\Helpers\Formatter;

$pageTitle = 'Investor Directory - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Investor Directory</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Manage user profiles, operational states, KYC verification and live account balances.</p>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-4 mb-6">
    <form action="/admin/users" method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                placeholder="Search by name, email, phone or referral code..."
                class="w-full pl-9 pr-4 py-2 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <select name="filter" class="px-3 py-2 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:outline-none">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="suspended" <?= $filter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#111827] text-white rounded-xl text-xs font-semibold hover:bg-gray-800 transition">
                Filter
            </button>
            <?php if (!empty($search) || $filter !== 'all'): ?>
                <a href="/admin/users" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-xl text-xs font-semibold hover:bg-gray-200 transition">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Investor</th>
                    <th class="px-6 py-3.5">Contact</th>
                    <th class="px-6 py-3.5">IP / Device</th>
                    <th class="px-6 py-3.5">Available Balance</th>
                    <th class="px-6 py-3.5">Invested Balance</th>
                    <th class="px-6 py-3.5">KYC Status</th>
                    <th class="px-6 py-3.5">Account Status</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B7280]">
                            No registered users found matching the query.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $u['id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27] flex items-center space-x-2">
                                    <span><?= htmlspecialchars($u['name']) ?></span>
                                </a>
                                <div class="text-[10px] text-[#6B7280] font-mono">Ref: <?= htmlspecialchars($u['referral_code']) ?> &bull; #<?= $u['id'] ?></div>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <div><?= htmlspecialchars($u['email']) ?></div>
                                <div class="text-[10px] text-gray-400"><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></div>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280] text-[10px] font-mono">
                                <div><?= htmlspecialchars($u['registration_ip'] ?? 'N/A') ?></div>
                                <div class="text-[9px] text-gray-400"><?= htmlspecialchars($u['device_fingerprint'] ? substr($u['device_fingerprint'], 0, 12) . '...' : 'N/A') ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold font-mono text-[#111827]">
                                <?= Formatter::currency((float)($u['available_balance'] ?? 0)) ?>
                            </td>
                            <td class="px-6 py-4 font-mono text-[#6B7280]">
                                <?= Formatter::currency((float)($u['invested_balance'] ?? 0)) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::kycBadge($u['kyc_status'] ?? 'not_submitted') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($u['status']) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/admin/users/detail?id=<?= $u['id'] ?>" class="px-3 py-1.5 bg-[#111827] hover:bg-gray-800 text-white rounded-lg font-semibold transition inline-flex items-center space-x-1">
                                    <span>360&deg; Overview</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total > $perPage): ?>
        <div class="p-4 bg-[#F8F9FA] border-t border-[#E5E7EB] flex items-center justify-between text-xs text-[#6B7280]">
            <span>Showing up to <?= $perPage ?> per page (Total: <?= $total ?> users)</span>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="/admin/users?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg">Previous</a>
                <?php endif; ?>
                <?php if ($page * $perPage < $total): ?>
                    <a href="/admin/users?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
