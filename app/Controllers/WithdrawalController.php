<?php
/**
 * CapitalNest Nepal - Withdrawal Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\WithdrawalService;
use App\Services\WalletService;
use App\Helpers\Security;
use Exception;

class WithdrawalController {
    private WithdrawalService $withdrawalService;
    private WalletService $walletService;

    public function __construct() {
        $this->withdrawalService = new WithdrawalService();
        $this->walletService = new WalletService();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $wallet = $this->walletService->getWallet($userId);
        $withdrawals = $this->withdrawalService->getUserWithdrawals($userId);
        $kycRecord = (new \App\Services\KycService())->getUserKyc($userId);
        $kycStatus = $kycRecord['status'] ?? 'not_submitted';
        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        require dirname(__DIR__) . '/Views/withdrawals/index.php';
    }

    public function submit(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $amount = (float)($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? 'Bank Transfer';
        $accountName = $_POST['account_name'] ?? '';
        $accountNumber = $_POST['account_number'] ?? '';
        $bankName = $_POST['bank_name'] ?? null;
        $branchName = $_POST['branch_name'] ?? null;
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $result = $this->withdrawalService->requestWithdrawal($userId, $amount, $method, $accountName, $accountNumber, $bankName, $branchName);
            if ($isAjax) {
                Security::jsonResponse([
                    'success' => true,
                    'message' => 'Withdrawal request queued for disbursement.',
                    'data' => $result
                ]);
            }
            header('Location: /withdrawals?success=' . urlencode('Withdrawal request queued. Funds reserved safely.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
            header('Location: /withdrawals?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
