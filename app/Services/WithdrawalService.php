<?php
/**
 * CapitalNest Nepal - Enterprise Withdrawal Management Service
 * Anti-Double-Spend Balance Reservation & Multi-Tier Audit Lifecycle
 */

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use App\Helpers\Security;
use PDO;
use Exception;

class WithdrawalService {
    private PDO $db;
    private WalletService $walletService;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
        $this->walletService = new WalletService($this->db);
    }

    /**
     * Submit Withdrawal request: DEDUCTS available balance immediately into escrow to prevent double-spending
     */
    public function requestWithdrawal(int $userId, float $amount, string $method, string $accountName, string $accountNumber, ?string $bankName = null, ?string $branchName = null): array {
        if ($amount < 500.00) {
            throw new Exception("Minimum withdrawal amount is NPR 500.00.");
        }
        if ($amount > 500000.00) {
            throw new Exception("Maximum single withdrawal limit is NPR 500,000.00.");
        }

        // Check user status
        $uStmt = $this->db->prepare("SELECT status FROM users WHERE id = :id LIMIT 1");
        $uStmt->execute([':id' => $userId]);
        $user = $uStmt->fetch();
        if (!$user || $user['status'] !== 'active') {
            throw new Exception("Account must be active to initiate withdrawals.");
        }

        $kycStmt = $this->db->prepare("SELECT status FROM kyc_requests WHERE user_id = :id ORDER BY id DESC LIMIT 1");
        $kycStmt->execute([':id' => $userId]);
        $kyc = $kycStmt->fetch();
        if (!$kyc || ($kyc['status'] ?? '') !== 'verified') {
            throw new Exception("Withdrawal is not allowed until your KYC is verified by compliance.");
        }

        $fee = round($amount * 0.01, 2); // 1% processing fee
        $netPayout = $amount - $fee;
        $withRef = Security::generateRef('WDL');

        // Deduct from wallet immediately (Lock & debit)
        $notes = "Withdrawal request to {$method} ({$accountName}, {$accountNumber})";
        $this->walletService->debitAvailable($userId, $amount, 'withdrawal', $withRef, $notes, 0.00);

        // Record withdrawal. MySQL and SQLite differ in column introspection syntax, so use the correct query for each driver.
        $legacyColumns = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            ? $this->db->query("PRAGMA table_info(withdrawals)")->fetchAll(PDO::FETCH_ASSOC)
            : $this->db->query("SHOW COLUMNS FROM withdrawals")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static fn(array $column): string => $column['Field'] ?? $column['name'], $legacyColumns);

        $insertFields = ['withdrawal_ref', 'user_id', 'amount', 'fee'];
        $params = [
            ':ref' => $withRef,
            ':user_id' => $userId,
            ':amount' => number_format($amount, 2, '.', ''),
            ':fee' => number_format($fee, 2, '.', ''),
        ];
        $placeholders = [':ref', ':user_id', ':amount', ':fee'];

        if (in_array('net_amount', $columnNames, true)) {
            $insertFields[] = 'net_amount';
            $params[':net_amount'] = number_format($netPayout, 2, '.', '');
            $placeholders[] = ':net_amount';
        }

        if (in_array('method', $columnNames, true)) {
            $insertFields[] = 'method';
            $params[':method'] = Security::sanitize($method);
            $placeholders[] = ':method';
        }
        if (in_array('payout_method', $columnNames, true)) {
            $insertFields[] = 'payout_method';
            $params[':payout_method'] = Security::sanitize($method);
            $placeholders[] = ':payout_method';
        }

        if (in_array('account_name', $columnNames, true)) {
            $insertFields[] = 'account_name';
            $params[':name'] = Security::sanitize($accountName);
            $placeholders[] = ':name';
        }
        if (in_array('account_number', $columnNames, true)) {
            $insertFields[] = 'account_number';
            $params[':num'] = Security::sanitize($accountNumber);
            $placeholders[] = ':num';
        }

        if (in_array('bank_name', $columnNames, true)) {
            $insertFields[] = 'bank_name';
            $params[':bank'] = $bankName ? Security::sanitize($bankName) : null;
            $placeholders[] = ':bank';
        }
        if (in_array('branch_name', $columnNames, true)) {
            $insertFields[] = 'branch_name';
            $params[':branch'] = $branchName ? Security::sanitize($branchName) : null;
            $placeholders[] = ':branch';
        }

        if (in_array('account_details', $columnNames, true)) {
            $insertFields[] = 'account_details';
            $params[':account_details'] = trim(implode(' | ', array_filter([
                $accountName,
                $accountNumber,
                $bankName,
                $branchName,
            ], static fn($value): bool => $value !== null && $value !== '')));
            $placeholders[] = ':account_details';
        }

        $insertFields[] = 'status';
        $params[':status'] = 'pending';
        $placeholders[] = ':status';

        $insertFields[] = 'created_at';
        $placeholders[] = 'CURRENT_TIMESTAMP';

        $stmt = $this->db->prepare(sprintf(
            "INSERT INTO withdrawals (%s) VALUES (%s)",
            implode(', ', $insertFields),
            implode(', ', $placeholders)
        ));
        $stmt->execute($params);
        $withdrawalId = (int)$this->db->lastInsertId();

        // Notification
        $this->createNotification(
            $userId,
            "Withdrawal Queued",
            "Withdrawal of NPR " . number_format($amount, 2) . " has been placed in processing queue.",
            "transaction"
        );

        return [
            'success' => true,
            'withdrawal_id' => $withdrawalId,
            'withdrawal_ref' => $withRef,
            'amount' => $amount,
            'fee' => $fee,
            'net_payout' => $netPayout
        ];
    }

    /**
     * Admin Mark Completed: Fund transferred to user bank/wallet
     */
    public function markCompleted(int $withdrawalId, int $adminId, string $adminNotes = ''): array {
        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM withdrawals WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $withdrawalId]);
            $withdrawal = $stmt->fetch();

            if (!$withdrawal) {
                throw new Exception("Withdrawal record not found.");
            }
            if ($withdrawal['status'] === 'completed') {
                throw new Exception("Withdrawal is already marked as completed.");
            }
            if ($withdrawal['status'] === 'rejected') {
                throw new Exception("Cannot complete a rejected withdrawal.");
            }

            $update = $this->db->prepare("
                UPDATE withdrawals 
                SET status = 'completed', processed_by = :admin_id, admin_notes = :notes, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $update->execute([
                ':admin_id' => $adminId,
                ':notes' => $adminNotes ?: 'Disbursed via bank clearing/RTGS',
                ':id' => $withdrawalId
            ]);

            $this->createNotification(
                (int)$withdrawal['user_id'],
                "Withdrawal Completed",
                "Your withdrawal of NPR " . number_format((float)$withdrawal['amount'], 2) . " has been successfully transferred to your account.",
                "transaction"
            );

            $this->logAdminActivity($adminId, 'withdrawal_complete', 'withdrawals', (string)$withdrawalId, [
                'amount' => $withdrawal['amount'],
                'ref' => $withdrawal['withdrawal_ref']
            ]);

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }

            return ['success' => true, 'message' => 'Withdrawal confirmed completed.'];
        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Admin Reject Withdrawal: SAFELY REFUNDS reserved balance back to user available balance!
     */
    public function rejectWithdrawal(int $withdrawalId, int $adminId, string $reason): array {
        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM withdrawals WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $withdrawalId]);
            $withdrawal = $stmt->fetch();

            if (!$withdrawal) {
                throw new Exception("Withdrawal record not found.");
            }
            if ($withdrawal['status'] === 'completed' || $withdrawal['status'] === 'rejected') {
                throw new Exception("Withdrawal cannot be rejected in current state: " . $withdrawal['status']);
            }

            $userId = (int)$withdrawal['user_id'];
            $amount = (float)$withdrawal['amount'];
            $ref = $withdrawal['withdrawal_ref'];

            // Refund balance safely
            $this->walletService->creditAvailable(
                $userId,
                $amount,
                'adjustment',
                $ref . '-REFUND',
                "Refund for rejected withdrawal: " . $reason
            );

            // Decrement total_withdrawals counter
            $adjStmt = $this->db->prepare("UPDATE wallets SET total_withdrawals = total_withdrawals - :amt WHERE user_id = :uid");
            $adjStmt->execute([':amt' => number_format($amount, 2, '.', ''), ':uid' => $userId]);

            // Update status
            $update = $this->db->prepare("
                UPDATE withdrawals 
                SET status = 'rejected', processed_by = :admin_id, admin_notes = :notes, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $update->execute([
                ':admin_id' => $adminId,
                ':notes' => $reason,
                ':id' => $withdrawalId
            ]);

            $this->createNotification(
                $userId,
                "Withdrawal Declined & Refunded",
                "Your withdrawal request of NPR " . number_format($amount, 2) . " was declined. NPR " . number_format($amount, 2) . " has been restored to your available balance. Reason: " . $reason,
                "transaction"
            );

            $this->logAdminActivity($adminId, 'withdrawal_reject', 'withdrawals', (string)$withdrawalId, [
                'amount' => $amount,
                'ref' => $ref,
                'reason' => $reason
            ]);

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }

            return ['success' => true, 'message' => 'Withdrawal rejected and funds refunded to user available balance.'];
        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getUserWithdrawals(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM withdrawals WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    private function createNotification(int $userId, string $title, string $message, string $type): void {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
            VALUES (:user_id, :title, :message, :type, 0, CURRENT_TIMESTAMP)
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
            VALUES (:admin_id, :action, :type, :id, :details, :ip, CURRENT_TIMESTAMP)
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
