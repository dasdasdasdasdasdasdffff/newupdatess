<?php
/**
 * CapitalNest Nepal - Investment Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\InvestmentService;
use App\Services\WalletService;
use App\Helpers\Security;
use Exception;

class InvestmentController {
    private InvestmentService $investmentService;
    private WalletService $walletService;

    public function __construct() {
        $this->investmentService = new InvestmentService();
        $this->walletService = new WalletService();
    }

    public function plans(): void {
        $user = AuthMiddleware::handle();
        $plans = $this->investmentService->getActivePlans();
        $wallet = $this->walletService->getWallet((int)$user['id']);
        require dirname(__DIR__) . '/Views/investments/plans.php';
    }

    public function myInvestments(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $investments = $this->investmentService->getUserInvestments($userId);
        require dirname(__DIR__) . '/Views/investments/my_investments.php';
    }

    public function processInvest(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $planId = (int)($_POST['plan_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $result = $this->investmentService->invest($userId, $planId, $amount);
            if ($isAjax) {
                Security::jsonResponse([
                    'success' => true,
                    'message' => 'Investment activated successfully!',
                    'data' => $result
                ]);
            }
            header('Location: /investments/my?success=' . urlencode('Investment activated successfully!'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
            header('Location: /investments/plans?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
