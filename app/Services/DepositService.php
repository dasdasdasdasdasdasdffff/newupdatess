<?php
/**
 * CapitalNest Nepal - Enterprise Deposit Management Service
 * Multi-Gateway (eSewa, Khalti, Bank, ConnectIPS) & Two-Phase Verification Protocol
 */

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use App\Helpers\Security;
use PDO;
use Exception;

class DepositService {
    private PDO $db;
    private WalletService $walletService;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
        $this->walletService = new WalletService($this->db);
    }

    /**
     * Submit user deposit request (Pending - NO balance increase)
     */
    public function submitDeposit(int $userId, float $amount, string $paymentMethod, string $referenceNumber, ?string $proofDocumentPath = null): array {
        if ($amount < 1000.00) {
            throw new Exception("Minimum deposit amount is NPR 1,000.00.");
        }
        if ($amount > 2000000.00) {
            throw new Exception("Maximum single deposit amount is NPR 2,000,000.00.");
        }

        $depositRef = Security::generateRef('DEP');

        $stmt = $this->db->prepare("
            INSERT INTO deposits 
            (deposit_ref, user_id, amount, fee, net_amount, transaction_id, payment_method, reference_number, proof_document_path, status, created_at)
            VALUES (:ref, :user_id, :amount, '0.00', :net_amount, :transaction_id, :method, :reference_num, :proof, 'pending', datetime('now'))
        ");
        $stmt->execute([
            ':ref' => $depositRef,
            ':user_id' => $userId,
            ':amount' => number_format($amount, 2, '.', ''),
            ':net_amount' => number_format($amount, 2, '.', ''),
            ':transaction_id' => $depositRef,
            ':method' => Security::sanitize($paymentMethod),
            ':reference_num' => Security::sanitize($referenceNumber),
            ':proof' => $proofDocumentPath
        ]);
        $depositId = (int)$this->db->lastInsertId();

        // Create user notification
        $this->createNotification(
            $userId,
            "Deposit Request Submitted",
            "Your deposit request of NPR " . number_format($amount, 2) . " via " . htmlspecialchars($paymentMethod) . " is under verification.",
            "transaction"
        );

        return [
            'success' => true,
            'deposit_id' => $depositId,
            'deposit_ref' => $depositRef,
            'status' => 'pending',
            'amount' => $amount
        ];
    }

    /**
     * Admin Approve Deposit: Executes atomic wallet credit & logs audit transaction
     */
    public function approveDeposit(int $depositId, int $adminId, string $adminNotes = ''): array {
        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM deposits WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $depositId]);
            $deposit = $stmt->fetch();

            if (!$deposit) {
                throw new Exception("Deposit record not found.");
            }
            if ($deposit['status'] !== 'pending') {
                throw new Exception("Deposit is already " . $deposit['status'] . ". Cannot approve again.");
            }

            $userId = (int)$deposit['user_id'];
            $amount = (float)$deposit['amount'];
            $depositRef = $deposit['deposit_ref'];

            // 1. Credit wallet via WalletService within same active transaction
            $creditResult = $this->walletService->creditAvailable(
                $userId,
                $amount,
                'deposit',
                $depositRef,
                "Approved Deposit via {$deposit['payment_method']} (Ref: {$deposit['reference_number']})"
            );

            // 2. Mark deposit approved
            $updateStmt = $this->db->prepare("
                UPDATE deposits 
                SET status = 'approved', reviewed_by = :admin_id, admin_notes = :notes, updated_at = datetime('now')
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':admin_id' => $adminId,
                ':notes' => $adminNotes ?: 'Approved by Treasury Officer',
                ':id' => $depositId
            ]);

            // 3. User notification
            $this->createNotification(
                $userId,
                "Deposit Approved & Credited",
                "Your deposit of NPR " . number_format($amount, 2) . " has been verified and added to your available balance.",
                "transaction"
            );

            // 4. Admin log
            $this->logAdminActivity($adminId, 'deposit_approve', 'deposits', (string)$depositId, [
                'amount' => $amount,
                'user_id' => $userId,
                'deposit_ref' => $depositRef
            ]);

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'message' => 'Deposit approved successfully and wallet balance credited.',
                'transaction_ref' => $creditResult['transaction_ref'],
                'new_balance' => $creditResult['new_balance']
            ];
        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Admin Reject Deposit: Zero balance change, records reason and notifies user
     */
    public function rejectDeposit(int $depositId, int $adminId, string $rejectionReason): array {
        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM deposits WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $depositId]);
            $deposit = $stmt->fetch();

            if (!$deposit) {
                throw new Exception("Deposit record not found.");
            }
            if ($deposit['status'] !== 'pending') {
                throw new Exception("Deposit is already " . $deposit['status'] . ". Cannot reject.");
            }

            $userId = (int)$deposit['user_id'];
            $amount = (float)$deposit['amount'];

            $updateStmt = $this->db->prepare("
                UPDATE deposits 
                SET status = 'rejected', reviewed_by = :admin_id, admin_notes = :notes, updated_at = datetime('now')
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':admin_id' => $adminId,
                ':notes' => $rejectionReason ?: 'Deposit verification failed.',
                ':id' => $depositId
            ]);

            $this->createNotification(
                $userId,
                "Deposit Rejected",
                "Your deposit request of NPR " . number_format($amount, 2) . " was declined. Reason: " . $rejectionReason,
                "transaction"
            );

            $this->logAdminActivity($adminId, 'deposit_reject', 'deposits', (string)$depositId, [
                'amount' => $amount,
                'user_id' => $userId,
                'reason' => $rejectionReason
            ]);

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'message' => 'Deposit rejected successfully. User wallet was not modified.'
            ];
        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getUserDeposits(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM deposits WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    private function createNotification(int $userId, string $title, string $message, string $type): void {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
            VALUES (:user_id, :title, :message, :type, 0, datetime('now'))
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':message' => $message,
            ':type' => $type
        ]);
    }

    private function logAdminActivity(int $adminId, string $action, string $targetType, string $targetId, array $details): void {
        $stmt = $this->db->prepare("
            INSERT INTO admin_activity_logs (admin_id, action, target_type, target_id, details, ip_address, created_at)
            VALUES (:admin_id, :action, :type, :id, :details, :ip, datetime('now'))
        ");
        $stmt->execute([
            ':admin_id' => $adminId,
            ':action' => $action,
            ':type' => $targetType,
            ':id' => $targetId,
            ':details' => json_encode($details),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    }
}
