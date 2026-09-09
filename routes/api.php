<?php
/**
 * CapitalNest Nepal - REST-style API Router
 */

declare(strict_types=1);

use App\Services\AuthService;
use App\Services\WalletService;
use App\Helpers\Security;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle API requests
if (str_starts_with($uri, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');

    // Health check
    if ($uri === '/api/health') {
        Security::jsonResponse(['status' => 'operational', 'system' => 'CapitalNest Nepal', 'php' => PHP_VERSION]);
    }

    // User live wallet API
    if ($uri === '/api/user/wallet') {
        $user = AuthService::getCurrentUser();
        if (!$user) Security::jsonResponse(['error' => 'Unauthorized'], 401);
        $wallet = (new WalletService())->getWallet((int)$user['id']);
        Security::jsonResponse(['success' => true, 'wallet' => $wallet]);
    }

    // Admin overview API
    if ($uri === '/api/admin/metrics') {
        $admin = AuthService::getCurrentAdmin();
        if (!$admin) Security::jsonResponse(['error' => 'Forbidden'], 403);
        $db = Config\Database::getConnection();
        Security::jsonResponse([
            'total_users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'pending_deposits' => (int)$db->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn(),
            'pending_withdrawals' => (int)$db->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn(),
            'pending_kyc' => (int)$db->query("SELECT COUNT(*) FROM kyc_requests WHERE status='pending'")->fetchColumn(),
        ]);
    }
}
