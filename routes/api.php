<?php
/**
 * CapitalNest Nepal - REST-style API Router
 */

declare(strict_types=1);

use App\Services\AuthService;
use App\Services\WalletService;
use App\Services\InvestmentService;
use App\Services\DepositService;
use App\Services\WithdrawalService;
use App\Helpers\Security;
use Config\Database;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Handle API requests
if (str_starts_with($uri, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . (getenv('API_CORS_ORIGIN') ?: '*'));
    header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

    if ($method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    /**
     * Read a JSON request body. Android clients should send Content-Type:
     * application/json for POST requests.
     */
    $body = static function (): array {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return $_POST;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            Security::jsonResponse(['error' => 'Request body must be valid JSON.'], 400);
        }
        return $decoded;
    };

    $tokenSecret = static function (): string {
        $secret = getenv('API_TOKEN_SECRET') ?: getenv('APP_KEY');
        if (!$secret || strlen($secret) < 32) {
            Security::jsonResponse(['error' => 'API token secret is not configured.'], 500);
        }
        return $secret;
    };

    $encodeToken = static function (int $userId) use ($tokenSecret): string {
        $payload = rtrim(strtr(base64_encode(json_encode([
            'sub' => $userId,
            'exp' => time() + (int)(getenv('API_TOKEN_TTL') ?: 2592000)
        ], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $payload, $tokenSecret(), true);
        return $payload . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    };

    $currentApiUser = static function () use ($tokenSecret): array {
        $header = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            Security::jsonResponse(['error' => 'Bearer token is required.'], 401);
        }

        $parts = explode('.', trim($matches[1]), 2);
        if (count($parts) !== 2) {
            Security::jsonResponse(['error' => 'Invalid bearer token.'], 401);
        }

        $payload = base64_decode(strtr($parts[0], '-_', '+/'), true);
        $signature = base64_decode(strtr($parts[1], '-_', '+/'), true);
        $expected = hash_hmac('sha256', $parts[0], $tokenSecret(), true);
        if ($payload === false || $signature === false || !hash_equals($expected, $signature)) {
            Security::jsonResponse(['error' => 'Invalid bearer token.'], 401);
        }

        $claims = json_decode($payload, true);
        if (!is_array($claims) || (int)($claims['exp'] ?? 0) < time() || (int)($claims['sub'] ?? 0) < 1) {
            Security::jsonResponse(['error' => 'Bearer token has expired or is invalid.'], 401);
        }

        $stmt = Database::getConnection()->prepare(
            "SELECT id, name, email, phone, referral_code, status, email_verified
             FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => (int)$claims['sub']]);
        $user = $stmt->fetch();
        if (!$user || $user['status'] !== 'active' || (int)$user['email_verified'] !== 1) {
            Security::jsonResponse(['error' => 'User account is unavailable.'], 401);
        }
        return $user;
    };

    $serviceError = static function (Throwable $error): void {
        error_log('API error: ' . $error->getMessage());
        Security::jsonResponse(['error' => $error->getMessage()], 422);
    };

    // Health check
    if ($uri === '/api/health') {
        Security::jsonResponse([
            'status' => 'operational',
            'system' => 'CapitalNest Nepal',
            'php' => PHP_VERSION,
            'api_version' => '1'
        ]);
    }

    if ($uri === '/api/auth/register' && $method === 'POST') {
        $input = $body();
        foreach (['name', 'email', 'phone', 'password'] as $field) {
            if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
                Security::jsonResponse(['error' => "The {$field} field is required."], 400);
            }
        }
        try {
            $result = (new AuthService())->register(
                (string)$input['name'],
                (string)$input['email'],
                (string)$input['phone'],
                (string)$input['password'],
                !empty($input['referral_code']) ? (string)$input['referral_code'] : null
            );
            Security::jsonResponse(['success' => true, 'data' => $result], 201);
        } catch (Throwable $error) {
            $serviceError($error);
        }
    }

    if ($uri === '/api/auth/login' && $method === 'POST') {
        $input = $body();
        if (empty($input['email']) || empty($input['password'])) {
            Security::jsonResponse(['error' => 'Email and password are required.'], 400);
        }
        try {
            $result = (new AuthService())->login((string)$input['email'], (string)$input['password']);
            $result['token'] = $encodeToken((int)$result['user']['id']);
            $result['token_type'] = 'Bearer';
            $result['expires_in'] = (int)(getenv('API_TOKEN_TTL') ?: 2592000);
            Security::jsonResponse($result);
        } catch (Throwable $error) {
            $serviceError($error);
        }
    }

    if ($uri === '/api/auth/me' && $method === 'GET') {
        Security::jsonResponse(['success' => true, 'user' => $currentApiUser()]);
    }

    if ($uri === '/api/auth/logout' && $method === 'POST') {
        Security::jsonResponse(['success' => true, 'message' => 'Discard the bearer token on the client.']);
    }

    // User live wallet API
    if ($uri === '/api/user/wallet' && $method === 'GET') {
        $user = $currentApiUser();
        $wallet = (new WalletService())->getWallet((int)$user['id']);
        Security::jsonResponse(['success' => true, 'wallet' => $wallet]);
    }

    if ($uri === '/api/user/transactions' && $method === 'GET') {
        $user = $currentApiUser();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 15)));
        $type = !empty($_GET['type']) ? (string)$_GET['type'] : null;
        $transactions = (new WalletService())->getTransactions((int)$user['id'], $page, $perPage, $type);
        Security::jsonResponse(['success' => true, 'transactions' => $transactions]);
    }

    if ($uri === '/api/investment-plans' && $method === 'GET') {
        Security::jsonResponse(['success' => true, 'plans' => (new InvestmentService())->getActivePlans()]);
    }

    if ($uri === '/api/investments' && $method === 'GET') {
        $user = $currentApiUser();
        Security::jsonResponse([
            'success' => true,
            'investments' => (new InvestmentService())->getUserInvestments((int)$user['id'])
        ]);
    }

    if ($uri === '/api/investments' && $method === 'POST') {
        $user = $currentApiUser();
        $input = $body();
        $planId = (int)($input['plan_id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        if ($planId < 1 || $amount <= 0) {
            Security::jsonResponse(['error' => 'plan_id and a positive amount are required.'], 400);
        }
        try {
            Security::jsonResponse([
                'success' => true,
                'investment' => (new InvestmentService())->invest((int)$user['id'], $planId, $amount)
            ], 201);
        } catch (Throwable $error) {
            $serviceError($error);
        }
    }

    if ($uri === '/api/deposits' && $method === 'GET') {
        $user = $currentApiUser();
        Security::jsonResponse([
            'success' => true,
            'deposits' => (new DepositService())->getUserDeposits((int)$user['id'])
        ]);
    }

    if ($uri === '/api/deposits' && $method === 'POST') {
        $user = $currentApiUser();
        $input = $body();
        $amount = (float)($input['amount'] ?? 0);
        $methodName = trim((string)($input['payment_method'] ?? ''));
        $reference = trim((string)($input['reference_number'] ?? ''));
        if ($amount <= 0 || $methodName === '' || $reference === '') {
            Security::jsonResponse([
                'error' => 'amount, payment_method, and reference_number are required.'
            ], 400);
        }
        try {
            Security::jsonResponse([
                'success' => true,
                'deposit' => (new DepositService())->submitDeposit(
                    (int)$user['id'],
                    $amount,
                    $methodName,
                    $reference,
                    !empty($input['proof_document_path']) ? (string)$input['proof_document_path'] : null
                )
            ], 201);
        } catch (Throwable $error) {
            $serviceError($error);
        }
    }

    if ($uri === '/api/withdrawals' && $method === 'GET') {
        $user = $currentApiUser();
        Security::jsonResponse([
            'success' => true,
            'withdrawals' => (new WithdrawalService())->getUserWithdrawals((int)$user['id'])
        ]);
    }

    if ($uri === '/api/withdrawals' && $method === 'POST') {
        $user = $currentApiUser();
        $input = $body();
        $amount = (float)($input['amount'] ?? 0);
        $methodName = trim((string)($input['method'] ?? ''));
        $accountName = trim((string)($input['account_name'] ?? ''));
        $accountNumber = trim((string)($input['account_number'] ?? ''));
        if ($amount <= 0 || $methodName === '' || $accountName === '' || $accountNumber === '') {
            Security::jsonResponse([
                'error' => 'amount, method, account_name, and account_number are required.'
            ], 400);
        }
        try {
            Security::jsonResponse([
                'success' => true,
                'withdrawal' => (new WithdrawalService())->requestWithdrawal(
                    (int)$user['id'],
                    $amount,
                    $methodName,
                    $accountName,
                    $accountNumber,
                    !empty($input['bank_name']) ? (string)$input['bank_name'] : null,
                    !empty($input['branch_name']) ? (string)$input['branch_name'] : null
                )
            ], 201);
        } catch (Throwable $error) {
            $serviceError($error);
        }
    }

    // Admin overview API
    if ($uri === '/api/admin/metrics' && $method === 'GET') {
        $admin = AuthService::getCurrentAdmin();
        if (!$admin) Security::jsonResponse(['error' => 'Forbidden'], 403);
        $db = Database::getConnection();
        Security::jsonResponse([
            'total_users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'pending_deposits' => (int)$db->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn(),
            'pending_withdrawals' => (int)$db->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn(),
            'pending_kyc' => (int)$db->query("SELECT COUNT(*) FROM kyc_requests WHERE status='pending'")->fetchColumn(),
        ]);
    }

    Security::jsonResponse(['error' => 'API endpoint not found.'], 404);
}
