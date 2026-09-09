<?php
/**
 * CapitalNest Nepal - Account & Compliance Notifications
 */
use App\Helpers\Formatter;

$pageTitle = 'Notifications - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Notifications & Broadcasts</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Real-time alerts regarding deposit approvals, investment returns and system broadcasts.</p>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden max-w-4xl mx-auto">
    <div class="p-5 border-b border-[#E5E7EB] flex items-center justify-between">
        <h2 class="text-base font-bold text-[#111827]">Notification Feed</h2>
        <span class="text-xs text-[#6B7280]">All marked as read</span>
    </div>

    <div class="divide-y divide-[#F3F4F6]">
        <?php if (empty($notifications)): ?>
            <div class="py-12 text-center text-xs text-[#6B7280]">
                You have no notifications at this time.
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="p-5 hover:bg-gray-50 transition flex items-start space-x-4">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-[#FEF9EE] text-[#C59B27] border border-[#F3E8C6]">
                        <?php if ($n['type'] === 'kyc'): ?>
                            <i data-lucide="file-badge-2" class="w-4 h-4"></i>
                        <?php elseif ($n['type'] === 'deposit' || $n['type'] === 'withdrawal'): ?>
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        <?php elseif ($n['type'] === 'investment'): ?>
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        <?php else: ?>
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-[#111827]"><?= htmlspecialchars($n['title']) ?></h3>
                            <span class="text-[10px] text-[#9CA3AF]"><?= Formatter::dateTime($n['created_at']) ?></span>
                        </div>
                        <p class="text-xs text-[#6B7280] mt-1 leading-relaxed"><?= htmlspecialchars($n['message']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
