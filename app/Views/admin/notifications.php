<?php
/**
 * CapitalNest Nepal - Admin Broadcast & Direct Notifications
 */
use App\Helpers\Formatter;

$pageTitle = 'Broadcasts & Alerts - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#111827] tracking-tight">Broadcasts & Client Communications</h1>
    <p class="text-xs text-[#6B7280] mt-0.5">Send targeted compliance messages to individual investors or platform-wide market bulletins.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Dispatch Form -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
        <h2 class="text-base font-bold text-[#111827] mb-1">Dispatch Notice</h2>
        <p class="text-xs text-[#6B7280] mb-6">Dispatched alerts appear in user dashboard notification centers immediately.</p>

        <form action="/admin/notifications/send" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <div>
                <label for="nRecipient" class="block text-xs font-semibold text-[#111827] mb-1">Target Audience</label>
                <select id="nRecipient" name="recipient" class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                    <option value="all">Global Broadcast (All Active Investors)</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>">Specific: <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="nTitle" class="block text-xs font-semibold text-[#111827] mb-1">Notice Headline</label>
                <input id="nTitle" name="title" type="text" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none"
                    placeholder="e.g. Nepal Rastra Bank Policy Update">
            </div>

            <div>
                <label for="nType" class="block text-xs font-semibold text-[#111827] mb-1">Alert Category</label>
                <select id="nType" name="type" class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                    <option value="system">System / Announcement</option>
                    <option value="investment">Investment Offering</option>
                    <option value="kyc">Compliance / KYC</option>
                    <option value="deposit">Deposit & Treasury Notice</option>
                </select>
            </div>

            <div>
                <label for="nMessage" class="block text-xs font-semibold text-[#111827] mb-1">Message Content</label>
                <textarea id="nMessage" name="message" rows="4" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs text-[#111827] focus:border-[#C59B27] focus:outline-none"
                    placeholder="Type official notification body..."></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-[#111827] hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2">
                    <i data-lucide="send" class="w-4 h-4 text-[#C59B27]"></i>
                    <span>Transmit Alert</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Broadcasts Feed -->
    <div class="lg:col-span-2 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E7EB]">
            <h2 class="text-base font-bold text-[#111827]">Recent Notification Logs</h2>
            <p class="text-xs text-[#6B7280]">Historical record of system and administrative alerts sent.</p>
        </div>

        <div class="divide-y divide-[#F3F4F6]">
            <?php if (empty($recentNotifications)): ?>
                <div class="py-12 text-center text-xs text-[#6B7280]">
                    No notifications sent yet.
                </div>
            <?php else: ?>
                <?php foreach ($recentNotifications as $n): ?>
                    <div class="p-5 hover:bg-gray-50 transition flex items-start space-x-4">
                        <div class="w-8 h-8 rounded-xl bg-[#FEF9EE] text-[#C59B27] border border-[#F3E8C6] flex items-center justify-center flex-shrink-0">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-[#111827]"><?= htmlspecialchars($n['title']) ?></h3>
                                <span class="text-[10px] text-[#9CA3AF]"><?= Formatter::dateTime($n['created_at']) ?></span>
                            </div>
                            <p class="text-xs text-[#6B7280] mt-1 leading-relaxed"><?= htmlspecialchars($n['message']) ?></p>
                            <div class="mt-2 text-[10px] text-[#9CA3AF]">
                                Recipient: <span class="font-semibold text-[#111827]"><?= empty($n['user_id']) ? 'Global Broadcast (All)' : 'User #' . htmlspecialchars((string)$n['user_id']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
