<?php
/**
 * CapitalNest Nepal - Wallet Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\WalletService;
use App\Helpers\Security;

class WalletController {
    private WalletService $walletService;

    public function __construct() {
        $this->walletService = new WalletService();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $wallet = $this->walletService->getWallet($userId);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $type = !empty($_GET['type']) ? $_GET['type'] : null;
        $transactionsData = $this->walletService->getTransactions($userId, $page, 15, $type);

        require dirname(__DIR__) . '/Views/wallet/index.php';
    }
}
