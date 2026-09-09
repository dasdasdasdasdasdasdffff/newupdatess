<?php
/**
 * CapitalNest Nepal - Enterprise Referral & Affiliate Engine
 * Server-Authoritative Anti-Fraud & Reward Commission Distribution
 */

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use App\Helpers\Security;
use PDO;
use Exception;

class ReferralService {
    private PDO $db;
    private WalletService $walletService;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
        $this->walletService = new WalletService($this->db);
    }

    /**
     * Generate unique referral code (e.g. CNP-7A2F9B)
     */
    public static function generateUniqueCode(): string {
        return 'CN' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Bind referral relationship upon user registration
     */
    public function bindReferral(int $referredUserId, string $referralCode): bool {
        $cleanCode = strtoupper(trim($referralCode));
        if (empty($cleanCode)) return false;

        // Find referrer
        $stmt = $this->db->prepare("SELECT id FROM users WHERE referral_code = :code LIMIT 1");
        $stmt->execute([':code' => $cleanCode]);
        $referrer = $stmt->fetch();

        if (!$referrer) return false;
        $referrerId = (int)$referrer['id'];

        // Prevent self-referral
        if ($referrerId === $referredUserId) return false;

        // Prevent duplicate bindings
        $chk = $this->db->prepare("SELECT id FROM referrals WHERE referred_user_id = :uid LIMIT 1");
        $chk->execute([':uid' => $referredUserId]);
        if ($chk->fetch()) return false;

        $referrerId = (int)$referrer['id'];
        $referrerReward = 100.00;
        $referredUserReward = 50.00;

        $ins = $this->db->prepare("
            INSERT INTO referrals (referrer_id, referred_user_id, referral_code, status, reward_amount, created_at)
            VALUES (:referrer, :referred, :code, 'pending', 0.00, CURRENT_TIMESTAMP)
        ");
        $ins->execute([
            ':referrer' => $referrerId,
            ':referred' => $referredUserId,
            ':code' => $cleanCode
        ]);

        return true;
    }

    public function activateVerifiedReferral(int $referredUserId): void {
        $stmt = $this->db->prepare("
            SELECT r.*, u.email_verified
            FROM referrals r
            JOIN users u ON u.id = r.referred_user_id
            WHERE r.referred_user_id = :uid AND r.status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([':uid' => $referredUserId]);
        $referral = $stmt->fetch();

        if (!$referral || (int)$referral['email_verified'] !== 1) {
            return;
        }

        $referrerId = (int)$referral['referrer_id'];
        $referrerReward = 100.00;
        $referredUserReward = 50.00;

        $this->creditRegistrationReward($referrerId, $referrerReward, 'Referral reward for verified partner');
        $this->creditRegistrationReward($referredUserId, $referredUserReward, 'Welcome reward after email verification');

        $update = $this->db->prepare("
            UPDATE referrals
            SET status = 'rewarded', reward_amount = :reward, rewarded_at = CURRENT_TIMESTAMP
            WHERE id = :id AND status = 'pending'
        ");
        $update->execute([
            ':reward' => $referrerReward,
            ':id' => (int)$referral['id']
        ]);

        $notification = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
            VALUES (:uid, :title, :message, 'referral', 0, CURRENT_TIMESTAMP)
        ");
        $notification->execute([
            ':uid' => $referrerId,
            ':title' => 'Referral Reward Credited',
            ':message' => 'NPR 100.00 has been added to your main balance for a successful referral.'
        ]);
        $notification->execute([
            ':uid' => $referredUserId,
            ':title' => 'Referral Welcome Reward',
            ':message' => 'NPR 50.00 has been added to your main balance after email verification.'
        ]);
    }

    private function creditRegistrationReward(int $userId, float $amount, string $notes): void {
        $walletStmt = $this->db->prepare('SELECT id, available_balance FROM wallets WHERE user_id = :user_id LIMIT 1');
        $walletStmt->execute([':user_id' => $userId]);
        $wallet = $walletStmt->fetch();
        if (!$wallet) {
            throw new Exception('User wallet not found while applying referral reward.');
        }

        $previousBalance = (float)$wallet['available_balance'];
        $newBalance = $previousBalance + $amount;
        $transactionRef = Security::generateRef('TXN-REF');
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $transaction = $this->db->prepare("
                INSERT INTO wallet_transactions
                (transaction_ref, user_id, wallet_id, type, amount, previous_balance, new_balance, reference)
                VALUES (:transaction_ref, :user_id, :wallet_id, 'referral', :amount, :previous_balance, :new_balance, :reference)
            ");
            $transaction->execute([
                ':transaction_ref' => $transactionRef,
                ':user_id' => $userId,
                ':wallet_id' => (int)$wallet['id'],
                ':amount' => $amount,
                ':previous_balance' => $previousBalance,
                ':new_balance' => $newBalance,
                ':reference' => $notes
            ]);
        } else {
            $transaction = $this->db->prepare("
                INSERT INTO wallet_transactions
                (transaction_ref, user_id, type, amount, fee, previous_balance, new_balance, reference, notes, status)
                VALUES (:transaction_ref, :user_id, 'referral', :amount, '0.00', :previous_balance, :new_balance, :reference, :notes, 'completed')
            ");
            $transaction->execute([
                ':transaction_ref' => $transactionRef,
                ':user_id' => $userId,
                ':amount' => $amount,
                ':previous_balance' => $previousBalance,
                ':new_balance' => $newBalance,
                ':reference' => $transactionRef,
                ':notes' => $notes
            ]);
        }

        $update = $this->db->prepare('UPDATE wallets SET available_balance = :balance, total_earnings = total_earnings + :amount WHERE user_id = :user_id');
        $update->execute([
            ':balance' => $newBalance,
            ':amount' => $amount,
            ':user_id' => $userId
        ]);
    }

    /**
     * Process referral bonus when referee makes an investment
     */
    public function processReferralRewardOnInvestment(int $refereeUserId, int $investmentId, float $investmentAmount): void {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as referee_name
            FROM referrals r
            JOIN users u ON r.referred_user_id = u.id
            WHERE r.referred_user_id = :uid
              AND r.status IN ('pending', 'rewarded')
              AND NOT EXISTS (
                  SELECT 1 FROM referral_earnings re
                  WHERE re.referral_id = r.id
              )
            LIMIT 1
        ");
        $stmt->execute([':uid' => $refereeUserId]);
        $referral = $stmt->fetch();

        if (!$referral) return; // Already rewarded or no referrer

        $referrerId = (int)$referral['referrer_id'];
        $commissionRate = 5.00; // 5%
        $rewardAmount = round(($investmentAmount * $commissionRate) / 100.0, 2);

        if ($rewardAmount <= 0) return;

        $refId = (int)$referral['id'];
        $txRef = Security::generateRef('REF-BONUS');

        // Credit referrer wallet with atomic audit transaction
        $notes = "Referral commission (5%) from {$referral['referee_name']}'s investment of NPR " . number_format($investmentAmount, 2);
        $this->walletService->creditAvailable($referrerId, $rewardAmount, 'referral', $txRef, $notes);

        // Update referral record
        $upd = $this->db->prepare("
            UPDATE referrals 
            SET status = 'rewarded', reward_amount = :amt, rewarded_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $upd->execute([':amt' => number_format($rewardAmount, 2, '.', ''), ':id' => $refId]);

        // Insert referral earnings breakdown
        $earnStmt = $this->db->prepare("
            INSERT INTO referral_earnings (referral_id, user_id, from_user_id, investment_id, amount, commission_rate, status, created_at)
            VALUES (:ref_id, :uid, :from_uid, :inv_id, :amt, :rate, 'paid', CURRENT_TIMESTAMP)
        ");
        $earnStmt->execute([
            ':ref_id' => $refId,
            ':uid' => $referrerId,
            ':from_uid' => $refereeUserId,
            ':inv_id' => $investmentId,
            ':amt' => number_format($rewardAmount, 2, '.', ''),
            ':rate' => number_format($commissionRate, 2, '.', '')
        ]);

        // Notify referrer
        $notif = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
            VALUES (:uid, 'Referral Commission Credited', :msg, 'referral', 0, CURRENT_TIMESTAMP)
        ");
        $notif->execute([
            ':uid' => $referrerId,
            ':msg' => "Congratulations! You earned NPR " . number_format($rewardAmount, 2) . " as referral commission from {$referral['referee_name']}."
        ]);
    }

    /**
     * Get user referral statistics & history
     */
    public function getUserReferralSummary(int $userId): array {
        // User's referral code
        $uStmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = :id LIMIT 1");
        $uStmt->execute([':id' => $userId]);
        $user = $uStmt->fetch();
        $code = $user['referral_code'] ?? '';

        // Referrals list
        $listStmt = $this->db->prepare("
            SELECT r.*, u.name, u.email, u.name as referred_name, u.email as referred_email, u.created_at as joined_at
            FROM referrals r
            JOIN users u ON r.referred_user_id = u.id
            WHERE r.referrer_id = :uid
            ORDER BY r.id DESC
        ");
        $listStmt->execute([':uid' => $userId]);
        $referrals = $listStmt->fetchAll();

        $totalReferrals = count($referrals);
        $successful = 0;
        $totalEarned = 0.0;

        foreach ($referrals as $r) {
            if ($r['status'] === 'rewarded' || $r['status'] === 'successful') {
                $successful++;
                $totalEarned += (float)$r['reward_amount'];
            }
        }

        return [
            'referral_code' => $code,
            'total_referrals' => $totalReferrals,
            'successful_referrals' => $successful,
            'active_referrals' => $successful,
            'total_earnings' => $totalEarned,
            'history' => $referrals,
            'referrals' => $referrals,
            'earnings' => $this->getUserReferralEarnings($userId)
        ];
    }

    private function getUserReferralEarnings(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT re.*, u.name as from_user_name, i.amount as investment_amount,
                   re.amount as commission_amount
            FROM referral_earnings re
            JOIN users u ON re.from_user_id = u.id
            LEFT JOIN investments i ON re.investment_id = i.id
            WHERE re.user_id = :uid
            ORDER BY re.id DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}
