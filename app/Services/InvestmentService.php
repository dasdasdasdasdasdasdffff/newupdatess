<?php
/**
 * CapitalNest Nepal - Enterprise Investment Management Service
 * Manages Dynamic Plan Subscriptions, Accruals, Maturity Calculations & Safety
 */

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use App\Helpers\Security;
use PDO;
use Exception;
use DateTime;

class InvestmentService {
    private PDO $db;
    private WalletService $walletService;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
        $this->walletService = new WalletService($this->db);
    }

    /**
     * Get all active investment plans
     */
    public function getActivePlans(): array {
        $stmt = $this->db->query("SELECT * FROM investment_plans WHERE status = 'active' ORDER BY min_investment ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get all plans (admin)
     */
    public function getAllPlans(): array {
        $stmt = $this->db->query("SELECT * FROM investment_plans ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get plan by ID
     */
    public function getPlanById(int $planId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM investment_plans WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $planId]);
        $plan = $stmt->fetch();
        return $plan ?: null;
    }

    /**
     * Create Investment subscription with atomic wallet balance debit
     */
    public function invest(int $userId, int $planId, float $amount): array {
        $plan = $this->getPlanById($planId);
        if (!$plan) {
            throw new Exception("Investment plan not found.");
        }
        if ($plan['status'] !== 'active') {
            throw new Exception("This investment plan is currently inactive.");
        }

        $min = (float)$plan['min_investment'];
        $max = (float)$plan['max_investment'];
        if ($amount < $min || ($max > 0 && $amount > $max)) {
            throw new Exception("Investment amount must be between NPR " . number_format($min, 2) . " and NPR " . number_format($max, 2) . ".");
        }

        // Calculate maturity and returns
        $returnRate = (float)$plan['return_rate'];
        $durationDays = (int)$plan['duration_days'];
        $expectedProfit = ($amount * $returnRate) / 100.0;
        $totalExpectedReturn = $amount + $expectedProfit;

        $startDate = new DateTime();
        $endDate = (clone $startDate)->modify("+{$durationDays} days");
        $nextPayout = (clone $startDate)->modify("+30 days");
        if ($nextPayout > $endDate) {
            $nextPayout = clone $endDate;
        }

        $invRef = Security::generateRef('INV');

        $this->db->beginTransaction();
        try {
            // Keep the wallet debit and investment record in one transaction.
            $notes = "Invested in {$plan['name']} (Tenure: {$durationDays} days, Return: {$returnRate}%)";
            $this->walletService->debitAvailable($userId, $amount, 'investment', $invRef, $notes);

            $stmt = $this->db->prepare("
                INSERT INTO investments
                (investment_ref, user_id, plan_id, amount, return_rate, expected_return, accrued_profit, duration_days, total_paid_out, start_date, end_date, next_payout_date, status, created_at)
                VALUES (:ref, :user_id, :plan_id, :amount, :rate, :expected, '0.00', :duration_days, '0.00', :start, :end, :payout, 'active', CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                ':ref' => $invRef,
                ':user_id' => $userId,
                ':plan_id' => $planId,
                ':amount' => number_format($amount, 2, '.', ''),
                ':rate' => number_format($returnRate, 2, '.', ''),
                ':expected' => number_format($totalExpectedReturn, 2, '.', ''),
                ':duration_days' => $durationDays,
                ':start' => $startDate->format('Y-m-d H:i:s'),
                ':end' => $endDate->format('Y-m-d H:i:s'),
                ':payout' => $nextPayout->format('Y-m-d H:i:s')
            ]);
            $invId = (int)$this->db->lastInsertId();

            $refService = new ReferralService($this->db);
            $refService->processReferralRewardOnInvestment($userId, $invId, $amount);
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        return [
            'success' => true,
            'investment_id' => $invId,
            'investment_ref' => $invRef,
            'amount' => $amount,
            'expected_return' => $totalExpectedReturn,
            'end_date' => $endDate->format('M d, Y')
        ];
    }

    /**
     * Get user active and completed investments with calculated dynamic progress
     */
    public function getUserInvestments(int $userId, ?string $status = null): array {
        $sql = "
            SELECT i.*, p.name as plan_name, p.slug as plan_slug, p.duration_days, p.payout_frequency
            FROM investments i
            JOIN investment_plans p ON i.plan_id = p.id
            WHERE i.user_id = :user_id
        ";
        $params = [':user_id' => $userId];
        if ($status) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $investments = $stmt->fetchAll();

        // Calculate real time progress percentage based on start_date and end_date
        $now = time();
        foreach ($investments as &$inv) {
            $start = strtotime($inv['start_date']);
            $end = strtotime($inv['end_date']);
            $totalDuration = max(1, $end - $start);
            $elapsed = max(0, $now - $start);

            if ($inv['status'] === 'completed') {
                $inv['progress_percent'] = 100.0;
            } elseif ($inv['status'] === 'cancelled') {
                $inv['progress_percent'] = 0.0;
            } else {
                $progress = ($elapsed / $totalDuration) * 100.0;
                $inv['progress_percent'] = min(100.0, max(0.0, round($progress, 1)));
            }

            // Days remaining
            $secondsRemaining = max(0, $end - $now);
            $inv['days_remaining'] = (int)ceil($secondsRemaining / 86400);
        }

        return $investments;
    }
}
