<?php
/**
 * CapitalNest Nepal - Web Routes Definition
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\WalletController;
use App\Controllers\InvestmentController;
use App\Controllers\DepositController;
use App\Controllers\WithdrawalController;
use App\Controllers\KycController;
use App\Controllers\ReferralController;
use App\Controllers\SupportController;
use App\Controllers\NotificationController;
use App\Controllers\AdminController;
use App\Controllers\FileController;
use Config\Database;
use App\Middleware\AdminMiddleware;

return [
    // Public / Guest
    'GET /' => [AuthController::class, 'showLogin'],
    'GET /login' => [AuthController::class, 'showLogin'],
    'POST /login' => [AuthController::class, 'processLogin'],
    'GET /register' => [AuthController::class, 'showRegister'],
    'POST /register' => [AuthController::class, 'processRegister'],
    'GET /register/pending' => [AuthController::class, 'showPendingVerification'],
    'GET /resend-verification' => [AuthController::class, 'resendVerification'],
    'GET /verify-email' => [AuthController::class, 'verifyEmail'],
    'GET /forgot-password' => [AuthController::class, 'showForgotPassword'],
    'POST /forgot-password' => [AuthController::class, 'processForgotPassword'],
    'GET /reset-password' => [AuthController::class, 'showResetPassword'],
    'POST /reset-password' => [AuthController::class, 'processResetPassword'],
    'GET /logout' => [AuthController::class, 'logout'],
    'POST /logout' => [AuthController::class, 'logout'],
    'GET /account/password' => [AuthController::class, 'showChangePassword'],
    'POST /account/password' => [AuthController::class, 'processChangePassword'],

    // User Portal
    'GET /dashboard' => [DashboardController::class, 'index'],
    'GET /wallet' => [WalletController::class, 'index'],
    
    'GET /investments' => [InvestmentController::class, 'plans'],
    'GET /investments/plans' => [InvestmentController::class, 'plans'],
    'POST /investments/invest' => [InvestmentController::class, 'processInvest'],
    'GET /investments/my' => [InvestmentController::class, 'myInvestments'],

    'GET /deposits' => [DepositController::class, 'index'],
    'POST /deposits/submit' => [DepositController::class, 'submit'],

    'GET /withdrawals' => [WithdrawalController::class, 'index'],
    'POST /withdrawals/submit' => [WithdrawalController::class, 'submit'],

    'GET /kyc' => [KycController::class, 'index'],
    'POST /kyc/submit' => [KycController::class, 'submit'],

    'GET /referrals' => [ReferralController::class, 'index'],

    'GET /support' => [SupportController::class, 'index'],
    'POST /support/create' => [SupportController::class, 'create'],
    'GET /support/view' => function() {
        $id = (int)($_GET['id'] ?? 0);
        (new SupportController())->view($id);
    },
    'POST /support/reply' => function() {
        $id = (int)($_POST['ticket_id'] ?? 0);
        (new SupportController())->reply($id);
    },

    'GET /notifications' => [NotificationController::class, 'index'],

    // Admin Portal
    'GET /admin' => [AdminController::class, 'dashboard'],
    'GET /admin/login' => [AuthController::class, 'showAdminLogin'],
    'POST /admin/login' => [AuthController::class, 'processAdminLogin'],
    'GET /admin/logout' => [AuthController::class, 'adminLogout'],
    'POST /admin/logout' => [AuthController::class, 'adminLogout'],

    'GET /admin/dashboard' => [AdminController::class, 'dashboard'],
    'GET /admin/users' => [AdminController::class, 'users'],
    'GET /admin/devices' => [AdminController::class, 'devices'],
    'POST /admin/devices/action' => [AdminController::class, 'deviceAction'],
    'GET /admin/users/detail' => function() {
        $id = (int)($_GET['id'] ?? 0);
        (new AdminController())->userDetail($id);
    },
    'POST /admin/users/change-password' => function() {
        $id = (int)($_POST['user_id'] ?? 0);
        (new AdminController())->changeUserPassword($id);
    },
    'POST /admin/change-password' => [AdminController::class, 'changeOwnPassword'],
    'POST /admin/users/status' => function() {
        $id = (int)($_POST['user_id'] ?? 0);
        (new AdminController())->updateUserStatus($id);
    },

    'GET /admin/deposits' => [AdminController::class, 'deposits'],
    'POST /admin/deposits/approve' => [AdminController::class, 'approveDeposit'],
    'POST /admin/deposits/reject' => [AdminController::class, 'rejectDeposit'],

    'GET /admin/withdrawals' => [AdminController::class, 'withdrawals'],
    'POST /admin/withdrawals/complete' => [AdminController::class, 'completeWithdrawal'],
    'POST /admin/withdrawals/approve' => [AdminController::class, 'completeWithdrawal'],
    'POST /admin/withdrawals/reject' => [AdminController::class, 'rejectWithdrawal'],

    'GET /admin/kyc' => [AdminController::class, 'kyc'],
    'POST /admin/kyc/approve' => [AdminController::class, 'approveKyc'],
    'POST /admin/kyc/reject' => [AdminController::class, 'rejectKyc'],

    'GET /admin/investments' => [AdminController::class, 'investmentPlans'],
    'POST /admin/investments/save-plan' => [AdminController::class, 'savePlan'],
    'POST /admin/investments/plans/save' => [AdminController::class, 'savePlan'],
    'POST /admin/investments/plans/toggle' => function() {
        AdminMiddleware::handle();
        $planId = (int)($_POST['plan_id'] ?? 0);
        $db = Database::getConnection();
        $plan = $db->query("SELECT status FROM investment_plans WHERE id = " . $planId)->fetch();
        if ($plan) {
            $newStatus = ($plan['status'] === 'active') ? 'inactive' : 'active';
            $db->prepare("UPDATE investment_plans SET status = ? WHERE id = ?")->execute([$newStatus, $planId]);
        }
        header('Location: /admin/investments?success=' . urlencode('Plan status updated.'));
        exit;
    },

    'GET /admin/transactions' => [AdminController::class, 'transactions'],
    'GET /admin/referrals' => [AdminController::class, 'referrals'],
    'GET /admin/notifications' => [AdminController::class, 'notifications'],
    'POST /admin/notifications/send' => [AdminController::class, 'sendNotification'],

    'GET /admin/support' => [AdminController::class, 'support'],
    'GET /admin/support/view' => function() {
        $id = (int)($_GET['id'] ?? 0);
        (new AdminController())->viewSupport($id);
    },
    'POST /admin/support/reply' => function() {
        $id = (int)($_POST['ticket_id'] ?? 0);
        (new AdminController())->replySupport($id);
    },
    'POST /admin/support/status' => function() {
        AdminMiddleware::handle();
        $id = (int)($_POST['ticket_id'] ?? 0);
        $st = $_POST['status'] ?? 'open';
        $db = Database::getConnection();
        $db->prepare("UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$st, $id]);
        header("Location: /admin/support/view?id={$id}&success=" . urlencode('Status updated.'));
        exit;
    },

    'GET /admin/activity-logs' => [AdminController::class, 'activityLogs'],
    'GET /admin/settings' => [AdminController::class, 'settings'],
    'POST /admin/settings/update' => [AdminController::class, 'updateSettings'],
    'POST /admin/settings/save' => [AdminController::class, 'updateSettings'],

    // Secure Artifact Delivery
    'GET /secure-file' => [FileController::class, 'serve'],
];
