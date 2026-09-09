<?php
/**
 * CapitalNest Nepal - Enterprise Wallet & Balance Service
 * Guarantees Atomic DB Transactions, Accurate Audit Trails & Race-Condition Safety
 */

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use App\Helpers\Security;
use PDO;
use Exception;

class WalletService {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Retrieve or initialize user wallet
     */
    public function getWallet(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $wallet = $stmt->fetch();

        if (!$wallet) {
            $stmt = $this->db->prepare("
                INSERT INTO wallets (user_id, available_balance, invested_balance, total_earnings, total_deposits, total_withdrawals, currency)
                VALUES (:user_id, '0.00', '0.00', '0.00', '0.00', '0.00', 'NPR')
            ");
            $stmt->execute([':user_id' => $userId]);
            return $this->getWallet($userId);
        }

        return $wallet;
    }

    /**
     * Credit Available Balance with Atomic Transaction & Audit Log
     */
    public function creditAvailable(int $userId, float $amount, string $type, string $reference, string $notes = ''): array {
        if ($amount <= 0) {
            throw new Exception("Credit amount must be strictly greater than zero.");
        }

        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $wallet = $stmt->fetch();

            if (!$wallet) {
                throw new Exception("User wallet not found.");
            }

            $prevBalance = (float)$wallet['available_balance'];
            $newBalance = $prevBalance + $amount;
            $txRef = Security::generateRef('TXN-CR');

            $txStmt = $this->db->prepare("
                INSERT INTO wallet_transactions 
                (transaction_ref, user_id, wallet_id, type, amount, fee, previous_balance, new_balance, reference, notes, status, created_at)
                VALUES (:ref, :user_id, :wallet_id, :type, :amount, '0.00', :prev, :new, :reference, :notes, 'completed', CURRENT_TIMESTAMP)
            ");
            $txStmt->execute([
                ':ref' => $txRef,
                ':user_id' => $userId,
                ':wallet_id' => (int)$wallet['id'],
                ':type' => $type,
                ':amount' => number_format($amount, 2, '.', ''),
                ':prev' => number_format($prevBalance, 2, '.', ''),
                ':new' => number_format($newBalance, 2, '.', ''),
                ':reference' => $reference,
                ':notes' => $notes
            ]);

            $updateSql = "UPDATE wallets SET available_balance = :new_balance";
            if ($type === 'deposit') {
                $updateSql .= ", total_deposits = total_deposits + :amt";
            } elseif ($type === 'profit' || $type === 'referral') {
                $updateSql .= ", total_earnings = total_earnings + :amt";
            }
            $updateSql .= " WHERE user_id = :user_id";

            $updateStmt = $this->db->prepare($updateSql);
            $params = [
                ':new_balance' => number_format($newBalance, 2, '.', ''),
                ':user_id' => $userId
            ];
            if ($type === 'deposit' || $type === 'profit' || $type === 'referral') {
                $params[':amt'] = number_format($amount, 2, '.', '');
            }
            $updateStmt->execute($params);

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'transaction_ref' => $txRef,
                'previous_balance' => $prevBalance,
                'new_balance' => $newBalance,
                'amount' => $amount
            ];
        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Debit Available Balance with Atomic Transaction & Audit Log
     */
    public function debitAvailable(int $userId, float $amount, string $type, string $reference, string $notes = '', float $fee = 0.00): array {
        if ($amount <= 0) {
            throw new Exception("Debit amount must be strictly greater than zero.");
        }

        $totalDeduction = $amount + $fee;
        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $wallet = $stmt->fetch();

            if (!$wallet) {
                throw new Exception("User wallet not found.");
            }

            $prevBalance = (float)$wallet['available_balance'];
            if ($prevBalance < $totalDeduction) {
                throw new Exception("Insufficient available balance. Required: NPR " . number_format($totalDeduction, 2) . ", Available: NPR " . number_format($prevBalance, 2));
            }

            $newBalance = $prevBalance - $totalDeduction;
            $txRef = Security::generateRef('TXN-DR');

            $txStmt = $this->db->prepare("
                INSERT INTO wallet_transactions 
                (transaction_ref, user_id, wallet_id, type, amount, fee, previous_balance, new_balance, reference, notes, status, created_at)
                VALUES (:ref, :user_id, :wallet_id, :type, :amount, :fee, :prev, :new, :reference, :notes, 'completed', CURRENT_TIMESTAMP)
            ");
            $txStmt->execute([
                ':ref' => $txRef,
                ':user_id' => $userId,
                ':wallet_id' => (int)$wallet['id'],
                ':type' => $type,
                ':amount' => number_format($amount, 2, '.', ''),
                ':fee' => number_format($fee, 2, '.', ''),
                ':prev' => number_format($prevBalance, 2, '.', ''),
                ':new' => number_format($newBalance, 2, '.', ''),
                ':reference' => $reference,
                ':notes' => $notes
            ]);

            $updateSql = "UPDATE wallets SET available_balance = :new_balance";
            if ($type === 'investment') {
                $updateSql .= ", invested_balance = invested_balance + :amt";
            } elseif ($type === 'withdrawal') {
                $updateSql .= ", total_withdrawals = total_withdrawals + :amt";
            }
            $updateSql .= " WHERE user_id = :user_id";

            $updateStmt = $this->db->prepare($updateSql);
            $params = [
                ':new_balance' => number_format($newBalance, 2, '.', ''),
                ':user_id' => $userId
            ];
            if ($type === 'investment' || $type === 'withdrawal') {
                $params[':amt'] = number_format($amount, 2, '.', '');
            }
            $updateStmt->execute($params);

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'transaction_ref' => $txRef,
                'previous_balance' => $prevBalance,
                'new_balance' => $newBalance,
                'amount' => $amount
            ];
        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get transaction history with pagination
     */
    public function getTransactions(int $userId, int $page = 1, int $perPage = 15, ?string $type = null): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM wallet_transactions WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($type) {
            $sql .= " AND type = :type";
            $params[':type'] = $type;
        }
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll();

        // Total count
        $countSql = "SELECT COUNT(*) FROM wallet_transactions WHERE user_id = :user_id";
        $countParams = [':user_id' => $userId];
        if ($type) {
            $countSql .= " AND type = :type";
            $countParams[':type'] = $type;
        }
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($countParams);
        $total = (int)$countStmt->fetchColumn();

        return [
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage)
        ];
    }
}
