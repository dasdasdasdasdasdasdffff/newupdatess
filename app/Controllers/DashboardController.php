<?php
/**
 * CapitalNest Nepal - User Dashboard Controller
 * Aggregates Real-Time Wallet Balances, Active Plan Accruals & Transaction Summaries
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\WalletService;
use App\Services\InvestmentService;
use App\Services\ReferralService;
use App\Services\KycService;
use Config\Database;

class DashboardController {
    private WalletService $walletService;
    private InvestmentService $investmentService;
    private ReferralService $referralService;
    private KycService $kycService;

    public function __construct() {
        $this->walletService = new WalletService();
        $this->investmentService = new InvestmentService();
        $this->referralService = new ReferralService();
        $this->kycService = new KycService();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        // Live database calculations
        $wallet = $this->walletService->getWallet($userId);
        $investments = $this->investmentService->getUserInvestments($userId);
        $transactionsData = $this->walletService->getTransactions($userId, 1, 6);
        $referralSummary = $this->referralService->getUserReferralSummary($userId);
        $kyc = $this->kycService->getUserKyc($userId);
        $kycStatus = $kyc['status'] ?? 'not_submitted';

        // Filter active investments
        $activeInvestments = array_filter($investments, fn($i) => $i['status'] === 'active');

        // Total calculated profit from all completed or active investments
        $totalProfit = (float)$wallet['total_earnings'];
        $availableBalance = (float)$wallet['available_balance'];
        $investedBalance = (float)$wallet['invested_balance'];
        $netWorth = $availableBalance + $investedBalance;

        // Active plans count
        $activeCount = count($activeInvestments);

        // Unread notifications
        $db = Database::getConnection();
        $notifStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $notifStmt->execute([':uid' => $userId]);
        $unreadNotifs = (int)$notifStmt->fetchColumn();

        require dirname(__DIR__) . '/Views/dashboard/index.php';
    }
}
