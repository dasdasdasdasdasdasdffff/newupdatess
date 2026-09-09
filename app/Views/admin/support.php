<?php
/**
 * CapitalNest Nepal - Admin Support Desk
 */
use App\Helpers\Formatter;

$pageTitle = 'Support Inquiries - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Support Desk Queue</h1>
        <p class="text-xs text-[#6B7280] mt-0.5">Respond to investor inquiries, troubleshoot transaction references and manage customer service SLA.</p>
    </div>

    <!-- Status filter tabs -->
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl text-xs">
        <a href="/admin/support?status=open" class="px-3 py-1.5 rounded-lg <?= $status === 'open' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Open
        </a>
        <a href="/admin/support?status=in_progress" class="px-3 py-1.5 rounded-lg <?= $status === 'in_progress' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            In Progress
        </a>
        <a href="/admin/support?status=resolved" class="px-3 py-1.5 rounded-lg <?= $status === 'resolved' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Resolved
        </a>
        <a href="/admin/support?status=closed" class="px-3 py-1.5 rounded-lg <?= $status === 'closed' ? 'bg-white font-bold text-[#111827] shadow-sm' : 'text-[#6B7280]' ?>">
            Closed
        </a>
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-6 py-3.5">Ref / Subject</th>
                    <th class="px-6 py-3.5">Investor</th>
                    <th class="px-6 py-3.5">Category</th>
                    <th class="px-6 py-3.5">Priority</th>
                    <th class="px-6 py-3.5">Last Updated</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B7280]">
                            No support tickets in the <?= htmlspecialchars($status) ?> queue.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="/admin/support/view?id=<?= $t['id'] ?>" class="font-bold text-[#111827] hover:text-[#C59B27]">
                                    <?= htmlspecialchars($t['subject']) ?>
                                </a>
                                <div class="text-[10px] text-[#9CA3AF] font-mono"><?= htmlspecialchars($t['ticket_ref']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="/admin/users/detail?id=<?= $t['user_id'] ?>" class="font-bold text-[#111827] hover:underline">
                                    <?= htmlspecialchars($t['user_name'] ?? 'Investor') ?>
                                </a>
                                <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($t['user_email'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4 capitalize text-[#6B7280]">
                                <?= htmlspecialchars($t['category']) ?>
                            </td>
                            <td class="px-6 py-4 uppercase font-bold text-[10px] <?= $t['priority'] === 'urgent' ? 'text-rose-600' : ($t['priority'] === 'high' ? 'text-amber-600' : 'text-[#6B7280]') ?>">
                                <?= htmlspecialchars($t['priority']) ?>
                            </td>
                            <td class="px-6 py-4 text-[#6B7280]">
                                <?= Formatter::dateTime($t['updated_at'] ?? $t['created_at']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= Formatter::statusBadge($t['status']) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/admin/support/view?id=<?= $t['id'] ?>" class="px-3 py-1.5 bg-[#111827] hover:bg-gray-800 text-white rounded-lg font-semibold transition inline-flex items-center space-x-1">
                                    <span>Reply</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
