<?php
/**
 * CapitalNest Nepal - KYC Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\KycService;
use App\Helpers\Security;
use Exception;

class KycController {
    private KycService $kycService;

    public function __construct() {
        $this->kycService = new KycService();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $kyc = $this->kycService->getUserKyc($userId);
        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        require dirname(__DIR__) . '/Views/kyc/index.php';
    }

    public function submit(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $result = $this->kycService->submitKyc($userId, $_POST, $_FILES);
            if ($isAjax) {
                Security::jsonResponse([
                    'success' => true,
                    'message' => 'KYC submitted successfully. Awaiting compliance review.',
                    'data' => $result
                ]);
            }
            header('Location: /kyc?success=' . urlencode('KYC verification submitted successfully. Compliance verification takes 1-4 hours.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
            header('Location: /kyc?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
