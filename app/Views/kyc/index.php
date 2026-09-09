<?php
/**
 * CapitalNest Nepal - KYC Compliance & Identity Gateway
 */
use App\Helpers\Formatter;

$pageTitle = 'KYC Verification - CapitalNest Nepal';
require dirname(__DIR__) . '/layouts/header.php';
$kycStatus = $kyc['status'] ?? 'not_submitted';
?>

<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#111827] tracking-tight">KYC Identity Verification</h1>
            <p class="text-xs text-[#6B7280] mt-0.5">Mandatory anti-money laundering (AML) compliance under Nepal Rastra Bank guidelines.</p>
        </div>
        <div>
            <?= Formatter::kycBadge($kycStatus) ?>
        </div>
    </div>
</div>

<?php if ($kycStatus === 'verified'): ?>
    <!-- Verified State -->
    <div class="bg-white border border-emerald-200 rounded-2xl p-8 shadow-sm text-center max-w-xl mx-auto my-8">
        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-100">
            <i data-lucide="shield-check" class="w-8 h-8"></i>
        </div>
        <h2 class="text-xl font-bold text-[#111827]">Compliance Clearance Approved</h2>
        <p class="text-xs text-[#6B7280] mt-1 max-w-md mx-auto">
            Your identity has been verified against the official civil registry. Your account holds unrestricted investment allocations and express priority withdrawals.
        </p>

        <div class="mt-6 p-4 bg-[#F8F9FA] rounded-xl border border-[#E5E7EB] text-left text-xs space-y-2">
            <div class="flex justify-between">
                <span class="text-[#6B7280]">Document:</span>
                <span class="font-bold text-[#111827]"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $kyc['document_type']))) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#6B7280]">ID Number:</span>
                <span class="font-mono font-bold text-[#111827]"><?= htmlspecialchars($kyc['id_number']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#6B7280]">Verified At:</span>
                <span class="text-[#111827]"><?= Formatter::dateTime($kyc['reviewed_at']) ?></span>
            </div>
        </div>
    </div>

<?php elseif ($kycStatus === 'pending'): ?>
    <!-- Pending State -->
    <div class="bg-white border border-amber-200 rounded-2xl p-8 shadow-sm text-center max-w-xl mx-auto my-8">
        <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-amber-100">
            <i data-lucide="clock" class="w-8 h-8"></i>
        </div>
        <h2 class="text-xl font-bold text-[#111827]">Submission Under Review</h2>
        <p class="text-xs text-[#6B7280] mt-1 max-w-md mx-auto">
            Your identity documents were submitted on <?= Formatter::dateTime($kyc['submitted_at']) ?> and are currently being audited by compliance officers.
        </p>
        <div class="mt-6 p-3.5 bg-[#FEF9EE] border border-[#F3E8C6] text-[#C59B27] rounded-xl text-xs">
            Review turnaround is typically 1 to 4 business hours. No further action is required from you at this time.
        </div>
    </div>

<?php else: ?>
    <!-- Form State (Not submitted OR Rejected) -->

    <?php if ($kycStatus === 'rejected'): ?>
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 flex items-start space-x-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <div class="font-bold">Previous Submission Incomplete / Rejected</div>
                <div class="mt-0.5">Reason provided by compliance: <span class="font-semibold underline"><?= htmlspecialchars($kyc['rejection_reason'] ?? 'Document unreadable.') ?></span></div>
                <div class="mt-1 text-rose-700">Please provide clear, legible photo scans below to re-apply.</div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8 max-w-3xl mx-auto">
        <h2 class="text-base font-bold text-[#111827] mb-1">Submit Identity Documents</h2>
        <p class="text-xs text-[#6B7280] mb-6">All uploaded files are cryptographically salted and isolated from public web directories.</p>

        <form action="/kyc/submit" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <!-- Document Type & Number -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="document_type" class="block text-xs font-semibold text-[#111827] mb-1">Identification Type</label>
                    <select id="document_type" name="document_type" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-medium focus:border-[#C59B27] focus:outline-none">
                        <option value="citizenship">Nepalese Citizenship (Nagarikta)</option>
                        <option value="passport">Machine Readable Passport (MRP)</option>
                        <option value="national_id">National Identity Card (Rastriya Parichayapatra)</option>
                        <option value="driving_license">Driving License</option>
                    </select>
                </div>
                <div>
                    <label for="id_number" class="block text-xs font-semibold text-[#111827] mb-1">Document / Citizenship ID Number</label>
                    <input id="id_number" name="id_number" type="text" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs font-mono uppercase focus:border-[#C59B27] focus:outline-none"
                        placeholder="e.g. 27-01-78-01293">
                </div>
            </div>

            <!-- Full Name & Family Names -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="full_name" class="block text-xs font-semibold text-[#111827] mb-1">Full Legal Name</label>
                    <input id="full_name" name="full_name" type="text" required
                        value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>"
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                </div>
                <div>
                    <label for="father_name" class="block text-xs font-semibold text-[#111827] mb-1">Father's Name</label>
                    <input id="father_name" name="father_name" type="text" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                </div>
                <div>
                    <label for="mother_name" class="block text-xs font-semibold text-[#111827] mb-1">Mother's Name</label>
                    <input id="mother_name" name="mother_name" type="text"
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                </div>
            </div>

            <!-- DOB & Gender -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="dob" class="block text-xs font-semibold text-[#111827] mb-1">Date of Birth (AD)</label>
                    <input id="dob" name="dob" type="date" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                </div>
                <div>
                    <label for="gender" class="block text-xs font-semibold text-[#111827] mb-1">Gender</label>
                    <select id="gender" name="gender" class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Addresses -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="permanent_address" class="block text-xs font-semibold text-[#111827] mb-1">Permanent Address</label>
                    <input id="permanent_address" name="permanent_address" type="text" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none"
                        placeholder="District, Municipality, Ward No.">
                </div>
                <div>
                    <label for="current_address" class="block text-xs font-semibold text-[#111827] mb-1">Current Residential Address</label>
                    <input id="current_address" name="current_address" type="text" required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#E5E7EB] rounded-xl text-xs focus:border-[#C59B27] focus:outline-none"
                        placeholder="Current residence">
                </div>
            </div>

            <!-- File Uploads (Front, Back, Selfie) -->
            <div class="space-y-4 pt-2">
                <div class="text-xs font-bold text-[#111827] uppercase tracking-wider">Document Scans & Photo Verification</div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 border border-[#E5E7EB] rounded-xl text-center bg-[#F8F9FA]">
                        <label class="block text-xs font-bold text-[#111827] mb-1">Document Front *</label>
                        <input type="file" name="front_document" required accept="image/*,application/pdf"
                            class="w-full text-[11px] text-[#6B7280] file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-[#111827] file:text-white">
                        <span class="block text-[10px] text-[#9CA3AF] mt-1.5">Front side with photo & ID</span>
                    </div>

                    <div class="p-4 border border-[#E5E7EB] rounded-xl text-center bg-[#F8F9FA]">
                        <label class="block text-xs font-bold text-[#111827] mb-1">Document Back</label>
                        <input type="file" name="back_document" accept="image/*,application/pdf"
                            class="w-full text-[11px] text-[#6B7280] file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-[#111827] file:text-white">
                        <span class="block text-[10px] text-[#9CA3AF] mt-1.5">Back side / stamp page</span>
                    </div>

                    <div class="p-4 border border-[#E5E7EB] rounded-xl text-center bg-[#F8F9FA]">
                        <label class="block text-xs font-bold text-[#111827] mb-1">Selfie Verification *</label>
                        <input type="file" name="selfie" required accept="image/*"
                            class="w-full text-[11px] text-[#6B7280] file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-[#111827] file:text-white">
                        <span class="block text-[10px] text-[#9CA3AF] mt-1.5">Selfie holding your document</span>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-3 px-4 bg-[#111827] hover:bg-[#1F2937] text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#C59B27]"></i>
                    <span>Submit Complete KYC Package</span>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
