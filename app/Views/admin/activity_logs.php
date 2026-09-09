<?php
/**
 * CapitalNest Nepal - Master Security & Audit Activity Logs
 */
use App\Helpers\Formatter;

$pageTitle = 'Activity & Audit Trail - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Security & Activity Audit Logs</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Immutable audit recording every administrative approval, financial debit/credit, and KYC clearance.</p>
    </div>

    <!-- Filter by role -->
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
        <a href="/admin/activity-logs" class="px-3 py-1.5 rounded-lg <?= empty($userRole) ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">All Events</a>
        <a href="/admin/activity-logs?role=admin" class="px-3 py-1.5 rounded-lg <?= ($userRole === 'admin') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">Admin Ops Only</a>
        <a href="/admin/activity-logs?role=user" class="px-3 py-1.5 rounded-lg <?= ($userRole === 'user') ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">User Actions</a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Timestamp</th>
                    <th class="px-6 py-3.5">Actor</th>
                    <th class="px-6 py-3.5">Action Event</th>
                    <th class="px-6 py-3.5">Details</th>
                    <th class="px-6 py-3.5">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($logs['data'])): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-[#6B7280]">
                            No activity logs found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs['data'] as $l): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-[#6B7280] whitespace-nowrap">
                                <?= Formatter::dateTime($l['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($l['user_name'])): ?>
                                    <span class="font-bold text-[#111827]"><?= htmlspecialchars($l['user_name']) ?></span>
                                    <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded <?= ($l['user_role'] === 'admin') ? 'bg-[#111827] text-[#C59B27]' : 'bg-gray-100 text-gray-600' ?>">
                                        <?= htmlspecialchars($l['user_role']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">System</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-[#111827]">
                                <?= htmlspecialchars($l['action']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280] max-w-md break-words">
                                <?= htmlspecialchars($l['details'] ?? '') ?>
                            </td>
                            <td class="px-6 py-4 font-mono text-[11px] text-gray-400">
                                <?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($logs['totalPages'] > 1): ?>
        <div class="p-4 bg-[#F8F9FA] border-t border-[#E5E7EB] flex items-center justify-between text-xs text-[#6B7280]">
            <span>Page <?= $logs['page'] ?> of <?= $logs['totalPages'] ?> (<?= $logs['total'] ?> events logged)</span>
            <div class="flex space-x-2">
                <?php if ($logs['page'] > 1): ?>
                    <a href="/admin/activity-logs?page=<?= $logs['page'] - 1 ?><?= !empty($userRole) ? '&role=' . urlencode($userRole) : '' ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg">Previous</a>
                <?php endif; ?>
                <?php if ($logs['page'] < $logs['totalPages']): ?>
                    <a href="/admin/activity-logs?page=<?= $logs['page'] + 1 ?><?= !empty($userRole) ? '&role=' . urlencode($userRole) : '' ?>" class="px-3 py-1 bg-white border border-[#E5E7EB] rounded-lg">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
