<?php
/**
 * CapitalNest Nepal - Deposit Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\DepositService;
use App\Services\WalletService;
use App\Helpers\Security;
use Exception;

class DepositController {
    private DepositService $depositService;
    private WalletService $walletService;

    public function __construct() {
        $this->depositService = new DepositService();
        $this->walletService = new WalletService();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $wallet = $this->walletService->getWallet($userId);
        $deposits = $this->depositService->getUserDeposits($userId);

        $settingsRows = \Config\Database::getConnection()->query("SELECT setting_key, setting_value FROM settings ORDER BY id ASC")->fetchAll();
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[(string)$row['setting_key']] = (string)$row['setting_value'];
        }

        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        require dirname(__DIR__) . '/Views/deposits/index.php';
    }

    public function submit(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'eSewa';
        $referenceNumber = $_POST['reference_number'] ?? '';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        // Handle uploaded proof
        $proofPath = null;
        if (!empty($_FILES['proof_file']['tmp_name']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/proofs';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0700, true);
            }
            $ext = pathinfo($_FILES['proof_file']['name'], PATHINFO_EXTENSION);
            $fileName = sprintf('proof_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(6)), strtolower($ext));
            $destination = $uploadDir . '/' . $fileName;
            if (!move_uploaded_file($_FILES['proof_file']['tmp_name'], $destination)) {
                copy($_FILES['proof_file']['tmp_name'], $destination);
            }
            $proofPath = 'proofs/' . $fileName;
        }

        try {
            $result = $this->depositService->submitDeposit($userId, $amount, $paymentMethod, $referenceNumber, $proofPath);
            if ($isAjax) {
                Security::jsonResponse([
                    'success' => true,
                    'message' => 'Deposit request submitted. Pending verification by treasury.',
                    'data' => $result
                ]);
            }
            header('Location: /deposits?success=' . urlencode('Deposit request submitted successfully. Awaiting treasury confirmation.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
            header('Location: /deposits?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
