<?php
/**
 * CapitalNest Nepal - Support Ticket Conversation Thread
 */
use App\Helpers\Formatter;

$pageTitle = 'Ticket #' . htmlspecialchars($ticket['ticket_ref']) . ' - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-6">
    <a href="/support" class="text-xs font-semibold text-[#6B7280] hover:text-[#111827] inline-flex items-center space-x-1 mb-3">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to Tickets</span>
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-[#111827]"><?= htmlspecialchars($ticket['subject']) ?></h1>
            <div class="flex items-center space-x-2 text-xs text-[#6B7280] mt-1">
                <span class="font-mono">Ref: <?= htmlspecialchars($ticket['ticket_ref']) ?></span>
                <span>&bull;</span>
                <span class="capitalize">Category: <?= htmlspecialchars($ticket['category']) ?></span>
                <span>&bull;</span>
                <span>Opened: <?= Formatter::dateTime($ticket['created_at']) ?></span>
            </div>
        </div>
        <div>
            <?= Formatter::statusBadge($ticket['status']) ?>
        </div>
    </div>
</div>

<!-- Chat Message Stream -->
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 mb-6">
    <div class="space-y-6">
        <?php foreach ($messages as $msg): ?>
            <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
            <div class="flex items-start space-x-3 <?= $isAdmin ? 'flex-row-reverse space-x-reverse' : '' ?>">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0 <?= $isAdmin ? 'bg-[#111827] text-[#C59B27]' : 'bg-gray-100 text-[#111827] border border-[#E5E7EB]' ?>">
                    <?= $isAdmin ? 'OPS' : 'YOU' ?>
                </div>
                <div class="max-w-xl <?= $isAdmin ? 'bg-[#111827] text-white' : 'bg-[#F8F9FA] text-[#111827] border border-[#E5E7EB]' ?> p-4 rounded-2xl">
                    <div class="flex items-center justify-between text-[10px] <?= $isAdmin ? 'text-gray-400' : 'text-[#6B7280]' ?> mb-1">
                        <span class="font-bold"><?= $isAdmin ? 'CapitalNest Officer' : 'You' ?></span>
                        <span><?= Formatter::dateTime($msg['created_at']) ?></span>
                    </div>
                    <div class="text-xs leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($msg['message']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply box -->
    <?php if ($ticket['status'] !== 'closed'): ?>
        <form action="/support/reply" method="POST" class="mt-8 pt-6 border-t border-[#F3F4F6] space-y-3">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

            <div>
                <label for="replyMessage" class="block text-xs font-semibold text-[#111827] mb-1">Post Response</label>
                <textarea id="replyMessage" name="message" rows="3" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none"
                    placeholder="Type your reply here..."></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                    <i data-lucide="send" class="w-3.5 h-3.5 text-[#C59B27]"></i>
                    <span>Send Message</span>
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="mt-8 pt-6 border-t border-[#F3F4F6] text-center text-xs text-[#6B7280]">
            This support ticket has been closed. Please open a new ticket if you have new questions.
        </div>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
