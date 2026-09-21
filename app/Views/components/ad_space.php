<?php
$adCompact = $adCompact ?? false;
$adPopup = $adPopup ?? false;
?>
<a href="https://t.me/nestxbet" target="_blank" rel="noopener noreferrer"
    class="group block relative overflow-hidden rounded-2xl border-2 border-dashed border-[#C4BDF7] bg-gradient-to-r from-[#FAF9FF] to-[#F1EFFF] p-5 sm:p-6 transition hover:-translate-y-0.5 hover:border-[#7667EE] hover:shadow-lg hover:shadow-[#7667EE]/10 <?= $adCompact ? 'sm:p-4' : '' ?>">
    <div class="relative z-10 flex items-center gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-[#7667EE] shadow-sm <?= $adCompact ? 'h-10 w-10 rounded-lg' : '' ?>">
            <i data-lucide="sparkles" class="<?= $adCompact ? 'h-5 w-5' : 'h-6 w-6' ?>"></i>
        </div>
        <div class="min-w-0">
            <strong class="block font-bold tracking-tight text-[#37336B] <?= $adCompact ? 'text-sm' : 'text-lg' ?>">Place your ads here</strong>
            <span class="mt-0.5 block text-xs text-[#817FA0]"><?= $adCompact ? 'Reach our audience with your brand.' : 'Premium ad space available for your brand.' ?></span>
            <span class="mt-2 flex items-center gap-1 text-[11px] font-bold text-[#7667EE]">Contact on Telegram <b class="text-[#4B3DC2]">@nestxbet</b> <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></span>
        </div>
        <span class="ml-auto hidden shrink-0 rounded-full bg-white px-2.5 py-1 text-[8px] font-bold tracking-widest text-[#7667EE] shadow-sm sm:block">AVAILABLE</span>
    </div>
    <div class="absolute -right-16 -top-24 h-52 w-52 rounded-full bg-[#7667EE]/10"></div>
</a>
<span class="mt-2 block text-[9px] font-bold tracking-[0.18em] text-[#A5A5B5]">ADVERTISEMENT</span>

<?php if ($adPopup): ?>
<div id="ad-popup" class="fixed inset-0 z-50 grid place-items-center bg-[#262440]/50 p-5 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Place your ad">
    <div class="relative w-full max-w-sm rounded-2xl bg-white p-8 text-center shadow-2xl">
        <button type="button" onclick="document.getElementById('ad-popup').remove()" class="absolute right-4 top-4 grid h-7 w-7 place-items-center rounded-full bg-[#F5F4FA] text-[#86869B]" aria-label="Close ad popup">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
        <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-[#EEEBFF] text-[#7667EE]"><i data-lucide="megaphone" class="h-7 w-7"></i></div>
        <span class="text-[10px] font-bold tracking-[0.16em] text-[#7667EE]">PREMIUM AD SPACE</span>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-[#20213A]">Place your ads here</h2>
        <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-[#9091A3]">Reach thousands of active members with a premium ad placement on CapitalNest.</p>
        <a href="https://t.me/nestxbet" target="_blank" rel="noopener noreferrer" class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-[#7667EE] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#7667EE]/25">Contact @nestxbet on Telegram <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
    </div>
</div>
<?php endif; ?>
