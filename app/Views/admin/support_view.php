<?php
/**
 * CapitalNest Nepal - Admin Support Ticket Conversation & Resolution
 */
use App\Helpers\Formatter;

$pageTitle = 'Respond to Ticket #' . htmlspecialchars($ticket['ticket_ref']) . ' - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-6">
    <a href="/admin/support" class="text-xs font-semibold text-[#6B7280] hover:text-[#111827] inline-flex items-center space-x-1 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to Support Queue</span>
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-[#111827]"><?= htmlspecialchars($ticket['subject']) ?></h1>
            <div class="text-xs text-[#6B7280] mt-1 flex items-center space-x-3">
                <span>Ref: <strong class="font-mono text-[#111827]"><?= htmlspecialchars($ticket['ticket_ref']) ?></strong></span>
                <span>&bull;</span>
                <span>Investor: <a href="/admin/users/detail?id=<?= $ticket['user_id'] ?>" class="text-[#C59B27] font-semibold hover:underline"><?= htmlspecialchars($ticket['user_name'] ?? 'Investor') ?></a></span>
                <span>&bull;</span>
                <span class="capitalize">Category: <?= htmlspecialchars($ticket['category']) ?></span>
            </div>
        </div>

        <!-- Status update form -->
        <form action="/admin/support/status" method="POST" class="flex items-center space-x-2">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <select name="status" class="px-3 py-1.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-semibold focus:outline-none">
                <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
            <button type="submit" class="px-3 py-1.5 bg-[#111827] text-white rounded-xl text-xs font-semibold hover:bg-gray-800 transition">
                Update Status
            </button>
        </form>
    </div>
</div>

<!-- Chat Message Stream -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 mb-6">
    <div class="space-y-6">
        <?php foreach ($messages as $msg): ?>
            <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
            <div class="flex items-start space-x-3 <?= $isAdmin ? 'flex-row-reverse space-x-reverse' : '' ?>">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0 <?= $isAdmin ? 'bg-[#111827] text-[#C59B27]' : 'bg-gray-100 text-[#111827] border border-[#E5E7EB]' ?>">
                    <?= $isAdmin ? 'OPS' : 'USER' ?>
                </div>
                <div class="max-w-xl <?= $isAdmin ? 'bg-[#111827] text-white' : 'bg-[#F8F9FA] text-[#111827] border border-[#E5E7EB]' ?> p-4 rounded-2xl">
                    <div class="flex items-center justify-between text-[10px] <?= $isAdmin ? 'text-gray-400' : 'text-[#6B7280]' ?> mb-1">
                        <span class="font-bold"><?= $isAdmin ? 'CapitalNest Officer Response' : htmlspecialchars($ticket['user_name'] ?? 'Investor') ?></span>
                        <span><?= Formatter::dateTime($msg['created_at']) ?></span>
                    </div>
                    <div class="text-xs leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($msg['message']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Admin Reply box -->
    <form action="/admin/support/reply" method="POST" class="mt-8 pt-6 border-t border-[#F3F4F6] space-y-3">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

        <div>
            <label for="adminReply" class="block text-xs font-semibold text-[#111827] mb-1">Official Response</label>
            <textarea id="adminReply" name="message" rows="3" required
                class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none"
                placeholder="Type response to investor..."></textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-[#111827] hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                <i data-lucide="send" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                <span>Post Official Reply</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
