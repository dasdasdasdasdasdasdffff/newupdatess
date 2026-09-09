<?php
/**
 * CapitalNest Nepal - Enterprise Admin Operations Controller
 * Real-Time Banking Telemetry, Treasury Clearance & Compliance Review
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Services\AuthService;
use App\Services\DepositService;
use App\Services\WithdrawalService;
use App\Services\KycService;
use App\Services\WalletService;
use App\Services\InvestmentService;
use Config\Database;
use App\Helpers\Security;
use Exception;
use PDO;

class AdminController {
    private PDO $db;
    private DepositService $depositService;
    private WithdrawalService $withdrawalService;
    private KycService $kycService;
    private WalletService $walletService;
    private InvestmentService $investmentService;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->depositService = new DepositService($this->db);
        $this->withdrawalService = new WithdrawalService($this->db);
        $this->kycService = new KycService($this->db);
        $this->walletService = new WalletService($this->db);
        $this->investmentService = new InvestmentService($this->db);
    }

    /**
     * Admin Dashboard with LIVE Database Metrics
     */
    public function dashboard(): void {
        $admin = AdminMiddleware::handle();

        // 1. User counts
        $totalUsers = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE email_verified = 1")->fetchColumn();
        $activeUsers = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE email_verified = 1 AND status = 'active'")->fetchColumn();

        // 2. Pending operational queues
        $pendingKyc = (int)$this->db->query("SELECT COUNT(*) FROM kyc_requests WHERE status = 'pending'")->fetchColumn();
        $pendingDeposits = (int)$this->db->query("SELECT COUNT(*) FROM deposits WHERE status = 'pending'")->fetchColumn();
        $pendingWithdrawals = (int)$this->db->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();

        // 3. Financial aggregates
        $totalDeposited = (float)$this->db->query("SELECT COALESCE(SUM(amount), 0) FROM deposits WHERE status = 'approved'")->fetchColumn();
        $totalWithdrawn = (float)$this->db->query("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 'completed'")->fetchColumn();
        $totalInvested = (float)$this->db->query("SELECT COALESCE(SUM(invested_balance), 0) FROM wallets")->fetchColumn();
        $totalProfits = (float)$this->db->query("SELECT COALESCE(SUM(total_earnings), 0) FROM wallets")->fetchColumn();
        $totalAvailableInSystem = (float)$this->db->query("SELECT COALESCE(SUM(available_balance), 0) FROM wallets")->fetchColumn();

        // 4. Recent pending queues
        $recentDeposits = $this->db->query("
            SELECT d.*, u.name as user_name, u.email as user_email 
            FROM deposits d JOIN users u ON d.user_id = u.id AND u.email_verified = 1
            ORDER BY d.id DESC LIMIT 5
        ")->fetchAll();

        $recentWithdrawals = $this->db->query("
            SELECT w.*, u.name as user_name, u.email as user_email 
            FROM withdrawals w JOIN users u ON w.user_id = u.id AND u.email_verified = 1
            ORDER BY w.id DESC LIMIT 5
        ")->fetchAll();

        $recentKycs = $this->db->query("
            SELECT k.*, u.name as user_name, u.email as user_email 
            FROM kyc_requests k JOIN users u ON k.user_id = u.id AND u.email_verified = 1
            ORDER BY k.id DESC LIMIT 5
        ")->fetchAll();

        require dirname(__DIR__) . '/Views/admin/dashboard.php';
    }

    /**
     * Users Management
     */
    public function users(): void {
        $admin = AdminMiddleware::handle();

        $search = trim($_GET['search'] ?? '');
        $filter = $_GET['filter'] ?? 'all';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT u.*, w.available_balance, w.invested_balance,
            (SELECT status FROM kyc_requests WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as kyc_status,
            (SELECT COUNT(*) FROM referrals WHERE referrer_id = u.id) as referral_count
            FROM users u
            LEFT JOIN wallets w ON u.id = w.user_id
            WHERE u.email_verified = 1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR u.referral_code LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        if ($filter === 'active') {
            $sql .= " AND u.status = 'active'";
        } elseif ($filter === 'suspended') {
            $sql .= " AND u.status = 'suspended'";
        }

        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as cnt";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql .= " ORDER BY u.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/admin/users.php';
    }

    public function devices(): void {
        AdminMiddleware::handle();

        $search = trim((string)($_GET['search'] ?? ''));
        $deviceType = trim((string)($_GET['device_type'] ?? ''));
        $operatingSystem = trim((string)($_GET['operating_system'] ?? ''));
        $date = trim((string)($_GET['date'] ?? ''));
        $sql = "
            SELECT d.*, u.id AS account_id, u.name, u.email, u.last_login_at,
                (SELECT COUNT(*) FROM user_devices d2 WHERE d2.user_id = u.id) AS device_count
            FROM user_devices d
            JOIN users u ON u.id = d.user_id
            WHERE 1 = 1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR d.device_id LIKE :search OR d.ip_address LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        if ($deviceType !== '') {
            $sql .= " AND d.device_type = :device_type";
            $params[':device_type'] = $deviceType;
        }
        if ($operatingSystem !== '') {
            $sql .= " AND d.operating_system = :operating_system";
            $params[':operating_system'] = $operatingSystem;
        }
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $sql .= " AND DATE(d.created_at) = :device_date";
            $params[':device_date'] = $date;
        }
        $sql .= " ORDER BY d.last_seen_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $devices = $stmt->fetchAll();
        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/admin/devices.php';
    }

    public function deviceAction(): void {
        $admin = AdminMiddleware::handle();
        $action = (string)($_POST['action'] ?? '');
        $deviceId = trim((string)($_POST['device_id'] ?? ''));
        $ipAddress = trim((string)($_POST['ip_address'] ?? ''));
        $redirect = '/admin/devices';

        if ($deviceId !== '') {
            $deviceStmt = $this->db->prepare("SELECT * FROM user_devices WHERE device_id = :device_id LIMIT 1");
            $deviceStmt->execute([':device_id' => $deviceId]);
            $device = $deviceStmt->fetch();
            if (!$device) {
                header('Location: ' . $redirect . '?error=' . urlencode('Device not found.'));
                exit;
            }

            if ($action === 'revoke') {
                $this->db->prepare("DELETE FROM user_devices WHERE id = :id")->execute([':id' => $device['id']]);
                $this->logActivity((int)$admin['id'], 'revoke_device', 'user_devices', (string)$device['id'], ['device_id' => $deviceId, 'user_id' => $device['user_id']]);
                header('Location: ' . $redirect . '?success=' . urlencode('Device unlinked.'));
                exit;
            }

            if ($action === 'block_device') {
                $minutes = max(1, min(43200, (int)($_POST['minutes'] ?? 1440)));
                $until = date('Y-m-d H:i:s', time() + ($minutes * 60));
                $this->db->prepare("UPDATE user_devices SET blocked_until = :until, blocked_reason = :reason WHERE id = :id")
                    ->execute([':until' => $until, ':reason' => 'Blocked by admin', ':id' => $device['id']]);
                $this->logActivity((int)$admin['id'], 'block_device', 'user_devices', (string)$device['id'], ['device_id' => $deviceId, 'until' => $until]);
                header('Location: ' . $redirect . '?success=' . urlencode('Device blocked.'));
                exit;
            }

            if ($action === 'unblock_device') {
                $this->db->prepare("UPDATE user_devices SET blocked_until = NULL, blocked_reason = NULL WHERE id = :id")
                    ->execute([':id' => $device['id']]);
                $this->logActivity((int)$admin['id'], 'unblock_device', 'user_devices', (string)$device['id'], ['device_id' => $deviceId]);
                header('Location: ' . $redirect . '?success=' . urlencode('Device unblocked.'));
                exit;
            }
        }

        if ($action === 'block_ip' && filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $minutes = max(1, min(43200, (int)($_POST['minutes'] ?? 60)));
            $until = date('Y-m-d H:i:s', time() + ($minutes * 60));
            $stmt = $this->db->prepare("
                INSERT INTO security_ip_blocks (ip_address, blocked_until, reason, created_by, created_at)
                VALUES (:ip, :until, :reason, :admin, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE blocked_until = VALUES(blocked_until), reason = VALUES(reason), created_by = VALUES(created_by)
            ");
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $stmt = $this->db->prepare("
                    INSERT INTO security_ip_blocks (ip_address, blocked_until, reason, created_by, created_at)
                    VALUES (:ip, :until, :reason, :admin, CURRENT_TIMESTAMP)
                    ON CONFLICT(ip_address) DO UPDATE SET blocked_until = excluded.blocked_until, reason = excluded.reason, created_by = excluded.created_by
                ");
            }
            $stmt->execute([':ip' => $ipAddress, ':until' => $until, ':reason' => 'Blocked by admin', ':admin' => $admin['id']]);
            $this->logActivity((int)$admin['id'], 'block_ip', 'security_ip_blocks', $ipAddress, ['until' => $until]);
            header('Location: ' . $redirect . '?success=' . urlencode('IP address blocked temporarily.'));
            exit;
        }

        header('Location: ' . $redirect . '?error=' . urlencode('Invalid device security action.'));
        exit;
    }

    /**
     * Comprehensive User Detail View (360-degree banking overview)
     */
    public function userDetail(int $userId): void {
        $admin = AdminMiddleware::handle();

        $uStmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND email_verified = 1 LIMIT 1");
        $uStmt->execute([':id' => $userId]);
        $targetUser = $uStmt->fetch();
        if (!$targetUser) {
            header('Location: /admin/users?error=' . urlencode('User not found.'));
            exit;
        }

        $wallet = $this->walletService->getWallet($userId);
        $investments = $this->investmentService->getUserInvestments($userId);
        $deposits = $this->depositService->getUserDeposits($userId);
        $withdrawals = $this->withdrawalService->getUserWithdrawals($userId);
        $kyc = $this->kycService->getUserKyc($userId);
        $txs = $this->walletService->getTransactions($userId, 1, 20)['data'];

        // Profile
        $pStmt = $this->db->prepare("SELECT * FROM user_profiles WHERE user_id = :id LIMIT 1");
        $pStmt->execute([':id' => $userId]);
        $profile = $pStmt->fetch();

        // Referrals
        $rStmt = $this->db->prepare("
            SELECT r.*, u.name, u.email FROM referrals r 
            JOIN users u ON r.referred_user_id = u.id AND u.email_verified = 1
            WHERE r.referrer_id = :id
        ");
        $rStmt->execute([':id' => $userId]);
        $referrals = $rStmt->fetchAll();

        $deviceStmt = $this->db->prepare("SELECT * FROM user_devices WHERE user_id = :id ORDER BY last_seen_at DESC");
        $deviceStmt->execute([':id' => $userId]);
        $userDevices = $deviceStmt->fetchAll();

        // Admin Activity logs regarding this user
        $logStmt = $this->db->prepare("SELECT * FROM admin_activity_logs WHERE target_id = :tid ORDER BY id DESC LIMIT 15");
        $logStmt->execute([':tid' => (string)$userId]);
        $activityLogs = $logStmt->fetchAll();

        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/admin/user_detail.php';
    }

    /**
     * Change User Status (Active / Suspended)
     */
    public function updateUserStatus(int $userId): void {
        $admin = AdminMiddleware::handle();
        $newStatus = $_POST['status'] ?? 'active';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        if (!in_array($newStatus, ['active', 'suspended'], true)) {
            if ($isAjax) Security::jsonResponse(['error' => 'Invalid status.'], 400);
            header("Location: /admin/users/detail?id={$userId}");
            exit;
        }

        $stmt = $this->db->prepare("UPDATE users SET status = :st WHERE id = :id");
        $stmt->execute([':st' => $newStatus, ':id' => $userId]);

        // Log admin activity
        $this->logActivity((int)$admin['id'], 'update_user_status', 'users', (string)$userId, ['new_status' => $newStatus]);

        if ($isAjax) {
            Security::jsonResponse(['success' => true, 'message' => "User status updated to {$newStatus}."]);
        }
        header("Location: /admin/users/detail?id={$userId}&success=" . urlencode("User status updated to {$newStatus}."));
        exit;
    }

    public function changeUserPassword(int $userId): void {
        $admin = AdminMiddleware::handle();
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        try {
            $this->db->beginTransaction();
            $result = (new AuthService($this->db))->changeUserPassword($userId, '', $newPassword, $confirmPassword);
            $this->db->commit();
            $this->logActivity((int)$admin['id'], 'reset_user_password', 'users', (string)$userId, ['action' => 'manual_password_reset']);
            header('Location: /admin/users/detail?id=' . $userId . '&success=' . urlencode($result['message']));
            exit;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            header('Location: /admin/users/detail?id=' . $userId . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function changeOwnPassword(): void {
        $admin = AdminMiddleware::handle();
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        try {
            $result = (new AuthService($this->db))->changeAdminPassword((int)$admin['id'], $currentPassword, $newPassword, $confirmPassword);
            $this->logActivity((int)$admin['id'], 'change_own_password', 'admin_users', (string)$admin['id'], ['status' => 'updated']);
            header('Location: /admin/settings?success=' . urlencode($result['message']));
            exit;
        } catch (Exception $e) {
            header('Location: /admin/settings?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Deposits Queue & Approvals
     */
    public function deposits(): void {
        $admin = AdminMiddleware::handle();
        $filter = $_GET['status'] ?? 'all';

        $sql = "
            SELECT d.*, u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM deposits d
            JOIN users u ON d.user_id = u.id AND u.email_verified = 1
            WHERE 1=1
        ";
        if ($filter !== 'all') {
            $sql .= " AND d.status = :st";
        }
        $sql .= " ORDER BY d.id DESC";

        $stmt = $this->db->prepare($sql);
        if ($filter !== 'all') {
            $stmt->execute([':st' => $filter]);
        } else {
            $stmt->execute();
        }
        $deposits = $stmt->fetchAll();
        $status = $filter;
        $csrf = Security::generateCsrfToken();

        require dirname(__DIR__) . '/Views/admin/deposits.php';
    }

    public function approveDeposit(): void {
        $admin = AdminMiddleware::handle();
        $depositId = (int)($_POST['deposit_id'] ?? 0);
        $notes = $_POST['admin_notes'] ?? 'Approved by treasury';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = $this->depositService->approveDeposit($depositId, (int)$admin['id'], $notes);
            if ($isAjax) Security::jsonResponse($res);
            header('Location: /admin/deposits?success=' . urlencode('Deposit approved successfully.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            header('Location: /admin/deposits?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function rejectDeposit(): void {
        $admin = AdminMiddleware::handle();
        $depositId = (int)($_POST['deposit_id'] ?? 0);
        $reason = $_POST['reason'] ?? 'Invalid payment slip or reference not received.';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = $this->depositService->rejectDeposit($depositId, (int)$admin['id'], $reason);
            if ($isAjax) Security::jsonResponse($res);
            header('Location: /admin/deposits?success=' . urlencode('Deposit rejected. User wallet was not modified.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            header('Location: /admin/deposits?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Withdrawals Queue & Disbursements
     */
    public function withdrawals(): void {
        $admin = AdminMiddleware::handle();
        $filter = $_GET['status'] ?? 'all';

        $sql = "
            SELECT w.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
            wal.available_balance as current_balance
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id AND u.email_verified = 1
            LEFT JOIN wallets wal ON u.id = wal.user_id
            WHERE 1=1
        ";
        if ($filter !== 'all') {
            $sql .= " AND w.status = :st";
        }
        $sql .= " ORDER BY w.id DESC";

        $stmt = $this->db->prepare($sql);
        if ($filter !== 'all') {
            $stmt->execute([':st' => $filter]);
        } else {
            $stmt->execute();
        }
        $withdrawals = $stmt->fetchAll();
        $csrf = Security::generateCsrfToken();

        require dirname(__DIR__) . '/Views/admin/withdrawals.php';
    }

    public function completeWithdrawal(): void {
        $admin = AdminMiddleware::handle();
        $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
        $notes = $_POST['admin_notes'] ?? 'Disbursed via bank transfer';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = $this->withdrawalService->markCompleted($withdrawalId, (int)$admin['id'], $notes);
            if ($isAjax) Security::jsonResponse($res);
            header('Location: /admin/withdrawals?success=' . urlencode('Withdrawal confirmed disbursed.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            header('Location: /admin/withdrawals?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function rejectWithdrawal(): void {
        $admin = AdminMiddleware::handle();
        $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
        $reason = $_POST['reason'] ?? 'Incorrect beneficiary account details.';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = $this->withdrawalService->rejectWithdrawal($withdrawalId, (int)$admin['id'], $reason);
            if ($isAjax) Security::jsonResponse($res);
            header('Location: /admin/withdrawals?success=' . urlencode('Withdrawal rejected and funds refunded to user wallet.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            header('Location: /admin/withdrawals?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * KYC Compliance Queue
     */
    public function kyc(): void {
        $admin = AdminMiddleware::handle();
        $filter = $_GET['status'] ?? 'all';

        $sql = "
            SELECT k.*, u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM kyc_requests k
            JOIN users u ON k.user_id = u.id AND u.email_verified = 1
            WHERE 1=1
        ";
        if ($filter !== 'all') {
            $sql .= " AND k.status = :st";
        }
        $sql .= " ORDER BY k.id DESC";

        $stmt = $this->db->prepare($sql);
        if ($filter !== 'all') {
            $stmt->execute([':st' => $filter]);
        } else {
            $stmt->execute();
        }
        $kycList = $stmt->fetchAll();
        $status = $filter;
        $submissions = $kycList;
        $csrf = Security::generateCsrfToken();

        require dirname(__DIR__) . '/Views/admin/kyc.php';
    }

    public function approveKyc(): void {
        $admin = AdminMiddleware::handle();
        $kycId = (int)($_POST['kyc_id'] ?? 0);
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = $this->kycService->approveKyc($kycId, (int)$admin['id']);
            if ($isAjax) Security::jsonResponse($res);
            header('Location: /admin/kyc?success=' . urlencode('KYC approved and verified.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            header('Location: /admin/kyc?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function rejectKyc(): void {
        $admin = AdminMiddleware::handle();
        $kycId = (int)($_POST['kyc_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $res = $this->kycService->rejectKyc($kycId, (int)$admin['id'], $reason);
            if ($isAjax) Security::jsonResponse($res);
            header('Location: /admin/kyc?success=' . urlencode('KYC rejected with reason recorded.'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            header('Location: /admin/kyc?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Investment Plans Management
     */
    public function investmentPlans(): void {
        $admin = AdminMiddleware::handle();
        $plans = $this->investmentService->getAllPlans();
        $investments = $this->db->query("
            SELECT i.*, u.name as user_name, u.email as user_email, p.name as plan_name
            FROM investments i
            JOIN users u ON i.user_id = u.id AND u.email_verified = 1
            JOIN investment_plans p ON i.plan_id = p.id
            ORDER BY i.id DESC LIMIT 50
        ")->fetchAll();

        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/admin/investments.php';
    }

    public function savePlan(): void {
        $admin = AdminMiddleware::handle();
        $planId = (int)($_POST['plan_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $min = (float)($_POST['min_investment'] ?? 0);
        $max = (float)($_POST['max_investment'] ?? 0);
        $rate = (float)($_POST['return_rate'] ?? 0);
        $duration = (int)($_POST['duration_days'] ?? 30);
        $payout = $_POST['payout_frequency'] ?? 'monthly';
        $status = $_POST['status'] ?? 'active';
        $desc = trim($_POST['description'] ?? '');
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));

        if ($planId > 0) {
            $stmt = $this->db->prepare("
                UPDATE investment_plans 
                SET name = :name, min_investment = :min, max_investment = :max, return_rate = :rate,
                    duration_days = :dur, payout_frequency = :payout, status = :status, description = :desc, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmt->execute([
                ':name' => $name, ':min' => $min, ':max' => $max, ':rate' => $rate,
                ':dur' => $duration, ':payout' => $payout, ':status' => $status, ':desc' => $desc, ':id' => $planId
            ]);
            $this->logActivity((int)$admin['id'], 'edit_plan', 'investment_plans', (string)$planId, ['name' => $name]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO investment_plans (name, slug, min_investment, max_investment, return_rate, duration_days, payout_frequency, status, description, created_at)
                VALUES (:name, :slug, :min, :max, :rate, :dur, :payout, :status, :desc, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                ':name' => $name, ':slug' => $slug, ':min' => $min, ':max' => $max, ':rate' => $rate,
                ':dur' => $duration, ':payout' => $payout, ':status' => $status, ':desc' => $desc
            ]);
            $this->logActivity((int)$admin['id'], 'create_plan', 'investment_plans', (string)$this->db->lastInsertId(), ['name' => $name]);
        }

        header('Location: /admin/investments?success=' . urlencode('Investment plan saved.'));
        exit;
    }

    /**
     * Transactions Explorer
     */
    public function transactions(): void {
        $admin = AdminMiddleware::handle();
        $type = $_GET['type'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        $sql = "
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM wallet_transactions t
            JOIN users u ON t.user_id = u.id AND u.email_verified = 1
            WHERE 1=1
        ";
        $params = [];
        if ($type !== 'all') {
            $sql .= " AND t.type = :type";
            $params[':type'] = $type;
        }
        if (!empty($search)) {
            $sql .= " AND (t.transaction_ref LIKE :s OR t.reference LIKE :s OR u.name LIKE :s OR u.email LIKE :s)";
            $params[':s'] = "%{$search}%";
        }
        $sql .= " ORDER BY t.id DESC LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/admin/transactions.php';
    }

    /**
     * Referrals Network
     */
    public function referrals(): void {
        $admin = AdminMiddleware::handle();

        $referrals = $this->db->query("
            SELECT r.*, 
            referrer.name as referrer_name, referrer.email as referrer_email,
            referred.name as referred_name, referred.email as referred_email
            FROM referrals r
            JOIN users referrer ON r.referrer_id = referrer.id AND referrer.email_verified = 1
            JOIN users referred ON r.referred_user_id = referred.id AND referred.email_verified = 1
            ORDER BY r.id DESC
        ")->fetchAll();

        $earnings = $this->db->query("
            SELECT re.*, re.user_id as referrer_id,
                   u.name as referrer_name, u.email as referrer_email,
                   ref.id as referred_id, ref.name as referred_name, ref.email as referred_email,
                   inv.amount as investment_amount,
                   re.amount as commission_amount
            FROM referral_earnings re
            JOIN users u ON re.user_id = u.id AND u.email_verified = 1
            JOIN users ref ON re.from_user_id = ref.id AND ref.email_verified = 1
            LEFT JOIN investments inv ON re.investment_id = inv.id
            ORDER BY re.id DESC LIMIT 50
        ")->fetchAll();

        require dirname(__DIR__) . '/Views/admin/referrals.php';
    }

    /**
     * Broadcast Notifications
     */
    public function notifications(): void {
        $admin = AdminMiddleware::handle();

        $notifications = $this->db->query("
            SELECT n.*, u.name as user_name, u.email as user_email
            FROM notifications n
            JOIN users u ON n.user_id = u.id AND u.email_verified = 1
            ORDER BY n.id DESC LIMIT 50
        ")->fetchAll();

        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/admin/notifications.php';
    }

    public function sendNotification(): void {
        $admin = AdminMiddleware::handle();
        $target = $_POST['target'] ?? 'all';
        $userId = (int)($_POST['user_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'system';

        if (empty($title) || empty($message)) {
            header('Location: /admin/notifications?error=' . urlencode('Title and message cannot be empty.'));
            exit;
        }

        if ($target === 'all') {
            $users = $this->db->query("SELECT id FROM users WHERE email_verified = 1 AND status = 'active'")->fetchAll();
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
                VALUES (:uid, :title, :message, :type, 0, CURRENT_TIMESTAMP)
            ");
            foreach ($users as $u) {
                $stmt->execute([
                    ':uid' => $u['id'],
                    ':title' => Security::sanitize($title),
                    ':message' => Security::sanitize($message),
                    ':type' => $type
                ]);
            }
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
                VALUES (:uid, :title, :message, :type, 0, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':title' => Security::sanitize($title),
                ':message' => Security::sanitize($message),
                ':type' => $type
            ]);
        }

        $this->logActivity((int)$admin['id'], 'send_notification', 'notifications', $target, ['title' => $title]);
        header('Location: /admin/notifications?success=' . urlencode('Notification dispatched successfully.'));
        exit;
    }

    /**
     * Support Tickets
     */
    public function support(): void {
        $admin = AdminMiddleware::handle();

        $tickets = $this->db->query("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM support_tickets t
            JOIN users u ON t.user_id = u.id AND u.email_verified = 1
            ORDER BY t.id DESC
        ")->fetchAll();

        require dirname(__DIR__) . '/Views/admin/support.php';
    }

    public function viewSupport(int $ticketId): void {
        $admin = AdminMiddleware::handle();

        $tStmt = $this->db->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email 
            FROM support_tickets t 
            JOIN users u ON t.user_id = u.id AND u.email_verified = 1
            WHERE t.id = :id LIMIT 1
        ");
        $tStmt->execute([':id' => $ticketId]);
        $ticket = $tStmt->fetch();

        $mStmt = $this->db->prepare("SELECT * FROM support_messages WHERE ticket_id = :tid ORDER BY id ASC");
        $mStmt->execute([':tid' => $ticketId]);
        $messages = $mStmt->fetchAll();

        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/admin/support_view.php';
    }

    public function replySupport(int $ticketId): void {
        $admin = AdminMiddleware::handle();
        $message = trim($_POST['message'] ?? '');
        $status = $_POST['status'] ?? 'resolved';

        if (!empty($message)) {
            $stmt = $this->db->prepare("
                INSERT INTO support_messages (ticket_id, sender_type, sender_id, message, created_at)
                VALUES (:tid, 'admin', :aid, :msg, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([':tid' => $ticketId, ':aid' => $admin['id'], ':msg' => Security::sanitize($message)]);
            $this->db->prepare("UPDATE support_tickets SET status = :st, updated_at = CURRENT_TIMESTAMP WHERE id = :id")->execute([':st' => $status, ':id' => $ticketId]);
        }

        header("Location: /admin/support/view?id={$ticketId}&success=" . urlencode('Response posted.'));
        exit;
    }

    /**
     * Activity Logs
     */
    public function activityLogs(): void {
        $admin = AdminMiddleware::handle();

        $logs = $this->db->query("
            SELECT l.*, a.name as admin_name, a.email as admin_email, a.role as admin_role
            FROM admin_activity_logs l
            JOIN admin_users a ON l.admin_id = a.id
            ORDER BY l.id DESC LIMIT 100
        ")->fetchAll();

        require dirname(__DIR__) . '/Views/admin/activity_logs.php';
    }

    /**
     * Settings
     */
    public function settings(): void {
        $admin = AdminMiddleware::handle('super_admin');

        $rows = $this->db->query("SELECT * FROM settings ORDER BY category ASC, id ASC")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[(string)$row['setting_key']] = (string)$row['setting_value'];
        }

        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/admin/settings.php';
    }

    public function updateSettings(): void {
        $admin = AdminMiddleware::handle('super_admin');

        $payload = $_POST['settings'] ?? $_POST;
        $qrKeys = ['esewa_qr_url', 'khalti_qr_url', 'fonepay_qr_url'];

        foreach ($qrKeys as $qrKey) {
            $uploaded = $_FILES['qr_uploads']['tmp_name'][$qrKey] ?? null;
            if (!empty($uploaded) && is_uploaded_file($uploaded)) {
                $current = $this->getSettingValue($qrKey);

                $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/qrs';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0775, true);
                }

                $ext = strtolower(pathinfo($_FILES['qr_uploads']['name'][$qrKey] ?? 'qr.png', PATHINFO_EXTENSION));
                if ($ext === '') {
                    $ext = 'png';
                }

                $fileName = $qrKey . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destination = $uploadDir . '/' . $fileName;

                if (move_uploaded_file($uploaded, $destination)) {
                    $payload[$qrKey] = 'qrs/' . $fileName;
                    $this->deleteStoredSettingFile($current);
                } else {
                    $payload[$qrKey] = $current;
                }
            }
        }

        foreach ($payload as $key => $val) {
            if (is_array($val)) {
                continue;
            }

            $key = (string)$key;
            if ($key === '_csrf_token' || $key === 'submit') {
                continue;
            }

            if (in_array($key, $qrKeys, true) && isset($_POST['clear_qr']) && is_array($_POST['clear_qr']) && in_array($key, $_POST['clear_qr'], true)) {
                $this->deleteStoredSettingFile($this->getSettingValue($key));
                $val = '';
            }

            if (in_array($key, $qrKeys, true) && !empty((string)$val) && !preg_match('#^(https?:)?//#i', (string)$val) && !str_starts_with((string)$val, '/')) {
                $val = trim((string)$val);
                $val = ltrim($val, './');
            }

            $this->saveSettingValue($key, trim((string)$val));
        }

        $this->logActivity((int)$admin['id'], 'update_settings', 'settings', 'global', ['keys' => array_keys($payload)]);
        header('Location: /admin/settings?success=' . urlencode('System settings updated successfully.'));
        exit;
    }

    private function saveSettingValue(string $key, string $value): void {
        $existing = $this->db->prepare("SELECT id FROM settings WHERE setting_key = :k LIMIT 1");
        $existing->execute([':k' => $key]);

        if ($existing->fetch()) {
            $stmt = $this->db->prepare("UPDATE settings SET setting_value = :val, updated_at = CURRENT_TIMESTAMP WHERE setting_key = :k");
            $stmt->execute([':val' => $value, ':k' => $key]);
            return;
        }

        $insert = $this->db->prepare("INSERT INTO settings (setting_key, setting_value, description, category, updated_at) VALUES (:k, :val, '', 'general', CURRENT_TIMESTAMP)");
        $insert->execute([':k' => $key, ':val' => $value]);
    }

    private function getSettingValue(string $key): string {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();
        return $row ? (string)$row['setting_value'] : '';
    }

    private function deleteStoredSettingFile(string $value): void {
        if ($value === '') {
            return;
        }

        $relative = trim($value);
        if (str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://') || str_starts_with($relative, '/')) {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/storage/uploads/' . ltrim($relative, './');
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function logActivity(int $adminId, string $action, string $targetType, string $targetId, array $details): void {
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
