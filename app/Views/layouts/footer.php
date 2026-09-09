<?php
/**
 * CapitalNest Nepal - User Portal Layout Footer
 */
?>
    </main>

    <!-- Standard Compliant Corporate Footer -->
    <footer class="bg-white border-t border-[#E5E7EB] mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-[#111827]">CAPITAL<span class="text-[#C59B27]">NEST</span> NEPAL</span>
                    <span class="text-xs text-[#9CA3AF]">|</span>
                    <span class="text-xs text-[#6B7280]">Licensed & Audited FinTech Portal</span>
                </div>
                <div class="flex items-center space-x-6 text-xs text-[#6B7280]">
                    <span>Security: AES-256 / SHA-256</span>
                    <span>Currency: Nepalese Rupee (NPR)</span>
                    <span class="text-[#111827] font-medium">&copy; <?= date('Y') ?> CapitalNest Nepal Pvt. Ltd.</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Render Lucide icons dynamically
        lucide.createIcons();
    </script>
</body>
</html>
