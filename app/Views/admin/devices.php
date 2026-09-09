<?php
$pageTitle = 'Device & Security - CapitalNest Admin';
require dirname(__DIR__) . '/layouts/admin_header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-[#111827]">Device &amp; Security</h1>
    <p class="text-xs text-[#6B7280] mt-1">Review registration devices, login metadata, and security actions.</p>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-4 mb-6">
    <form action="/admin/devices" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <input name="search" value="<?= htmlspecialchars($search) ?>" placeholder="User, email, device ID or IP"
            class="md:col-span-2 px-3 py-2 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs">
        <select name="device_type" class="px-3 py-2 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs">
            <option value="">All device types</option>
            <?php foreach (['desktop', 'mobile', 'tablet'] as $type): ?>
                <option value="<?= $type ?>" <?= $deviceType === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
            <?php endforeach; ?>
        </select>
        <input name="operating_system" value="<?= htmlspecialchars($operatingSystem) ?>" placeholder="Operating system"
            class="px-3 py-2 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs">
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>"
            class="px-3 py-2 bg-[#F8F9FA] border border-[#E5E7EB] rounded-xl text-xs">
        <button class="px-4 py-2 bg-[#111827] text-white rounded-xl text-xs font-semibold">Filter</button>
    </form>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#F8F9FA] text-[#6B7280] uppercase tracking-wider font-semibold border-b border-[#E5E7EB]">
                <tr>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Device</th>
                    <th class="px-4 py-3">IP / Browser / OS</th>
                    <th class="px-4 py-3">Registered</th>
                    <th class="px-4 py-3">Last login / seen</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3F4F6]">
                <?php foreach ($devices as $device): ?>
                    <?php $blocked = !empty($device['blocked_until']) && strtotime((string)$device['blocked_until']) > time(); ?>
                    <tr class="align-top">
                        <td class="px-4 py-4">
                            <a class="font-semibold hover:text-[#C59B27]" href="/admin/users/detail?id=<?= (int)$device['account_id'] ?>">
                                <?= htmlspecialchars($device['name']) ?>
                            </a>
                            <div class="text-[10px] text-[#6B7280]"><?= htmlspecialchars($device['email']) ?> · #<?= (int)$device['account_id'] ?></div>
                            <div class="text-[10px] text-[#6B7280]"><?= (int)$device['device_count'] ?> device(s)</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold"><?= htmlspecialchars((string)$device['device_type']) ?></div>
                            <div class="font-mono text-[10px] break-all max-w-[180px]"><?= htmlspecialchars((string)$device['device_id']) ?></div>
                            <?php if ($blocked): ?><span class="text-rose-600 font-semibold">Blocked until <?= htmlspecialchars((string)$device['blocked_until']) ?></span><?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-[#6B7280]">
                            <div class="font-mono"><?= htmlspecialchars((string)($device['ip_address'] ?: 'N/A')) ?></div>
                            <div><?= htmlspecialchars((string)($device['browser'] ?: 'Other')) ?></div>
                            <div><?= htmlspecialchars((string)($device['operating_system'] ?: 'Other')) ?></div>
                        </td>
                        <td class="px-4 py-4 text-[#6B7280]"><?= htmlspecialchars((string)$device['created_at']) ?></td>
                        <td class="px-4 py-4 text-[#6B7280]">
                            <div>Login: <?= htmlspecialchars((string)($device['last_login_at'] ?: 'N/A')) ?></div>
                            <div>Seen: <?= htmlspecialchars((string)$device['last_seen_at']) ?></div>
                        </td>
                        <td class="px-4 py-4 min-w-[190px]">
                            <form action="/admin/devices/action" method="POST" class="space-y-2">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="device_id" value="<?= htmlspecialchars((string)$device['device_id']) ?>">
                                <input type="hidden" name="ip_address" value="<?= htmlspecialchars((string)$device['ip_address']) ?>">
                                <input type="hidden" name="minutes" value="1440">
                                <?php if ($blocked): ?>
                                    <button name="action" value="unblock_device" class="w-full px-2 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg font-semibold">Unblock device</button>
                                <?php else: ?>
                                    <button name="action" value="block_device" class="w-full px-2 py-1.5 bg-amber-50 text-amber-800 rounded-lg font-semibold">Block device (24h)</button>
                                <?php endif; ?>
                                <button name="action" value="block_ip" class="w-full px-2 py-1.5 bg-rose-50 text-rose-700 rounded-lg font-semibold">Block IP (24h)</button>
                                <button name="action" value="revoke" onclick="return confirm('Unlink this device?')" class="w-full px-2 py-1.5 bg-gray-100 text-gray-700 rounded-lg font-semibold">Revoke / unlink</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($devices)): ?>
                    <tr><td colspan="6" class="px-4 py-10 text-center text-[#6B7280]">No device records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
