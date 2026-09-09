<?php
/**
 * CapitalNest Nepal - Enterprise Database Configuration
 * PDO with Prepared Statements & Transaction Support
 */

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $databaseUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: null;
        $connectionType = getenv('DB_CONNECTION') ?: ($databaseUrl ? 'mysql' : 'sqlite');

        if ($databaseUrl && !getenv('DB_HOST') && !getenv('MYSQLHOST')) {
            $parsed = parse_url((string)$databaseUrl);
            if (is_array($parsed) && isset($parsed['host'])) {
                putenv('DB_HOST=' . ($parsed['host'] ?? '127.0.0.1'));
                putenv('DB_PORT=' . ($parsed['port'] ?? '3306'));
                putenv('DB_DATABASE=' . ltrim((string)($parsed['path'] ?? '/capitalnest_db'), '/'));
                putenv('DB_USERNAME=' . ($parsed['user'] ?? 'root'));
                putenv('DB_PASSWORD=' . ($parsed['pass'] ?? ''));
            }
        }

        $host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DATABASE_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: getenv('MYSQL_DB') ?: 'capitalnest_db';
        $username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '');

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_TIMEOUT => 5
        ];

        try {
            if ($connectionType === 'mysql') {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                self::$instance = new PDO($dsn, $username, $password, $options);
                self::ensureDatabaseReady(self::$instance);
            } else {
                // Portable SQLite fallback for local verification
                $sqlitePath = self::getSqlitePath();
                $dsn = "sqlite:{$sqlitePath}";
                self::$instance = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                self::ensureSqliteColumns(self::$instance);
            }
        } catch (PDOException $e) {
            // Never switch a production deployment to a new empty local database.
            if ((getenv('APP_ENV') ?: 'production') === 'production' && $connectionType === 'mysql') {
                error_log("Production database connection failed: " . $e->getMessage());
                throw new Exception("Production database unavailable. Check the Railway MySQL connection.");
            }

            // SQLite fallback is only for explicit local development.
            try {
                $sqlitePath = self::getSqlitePath();
                $sqliteDir = dirname($sqlitePath);
                if (!is_dir($sqliteDir)) {
                    if (!mkdir($sqliteDir, 0755, true) && !is_dir($sqliteDir)) {
                        throw new Exception("Unable to create SQLite storage directory: {$sqliteDir}");
                    }
                }
                self::$instance = new PDO("sqlite:{$sqlitePath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                self::bootstrapSqlite(self::$instance);
            } catch (Exception $inner) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failure. Please check MySQL credentials.");
            }
        }

        return self::$instance;
    }

    private static function ensureDatabaseReady(PDO $pdo): void {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $hasUsersTable = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users' LIMIT 1")->fetchColumn();
            if ($hasUsersTable) {
                self::ensureMysqlSessionTable($pdo);
                self::ensureMysqlColumns($pdo);
                self::backfillMysqlReferrals($pdo);
                return;
            }

            $seedFile = dirname(__DIR__) . '/database/seed.php';
            if (file_exists($seedFile)) {
                require_once $seedFile;
            }
            self::ensureMysqlSessionTable($pdo);
            self::ensureMysqlColumns($pdo);
            self::backfillMysqlReferrals($pdo);
            return;
        }

        self::bootstrapSqlite($pdo);
    }

    private static function ensureMysqlColumns(PDO $pdo): void {
        $columns = [
            'deposits' => [
                'net_amount' => "DECIMAL(18, 2) NOT NULL DEFAULT '0.00' AFTER fee",
                'transaction_id' => 'VARCHAR(100) NULL DEFAULT NULL AFTER net_amount',
                'processed_by' => 'BIGINT UNSIGNED NULL DEFAULT NULL AFTER reviewed_by',
                'processed_at' => 'DATETIME NULL DEFAULT NULL AFTER processed_by',
            ],
            'investments' => [
                'duration_days' => 'INT NOT NULL DEFAULT 0 AFTER accrued_profit',
                'total_paid_out' => "DECIMAL(18, 2) NOT NULL DEFAULT '0.00' AFTER duration_days",
            ],
        ];

        foreach ($columns as $table => $tableColumns) {
            $tableExists = $pdo->prepare(
                "SELECT 1 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = :table LIMIT 1"
            );
            $tableExists->execute([':table' => $table]);
            if (!$tableExists->fetchColumn()) {
                continue;
            }

            $existing = $pdo->prepare(
                "SELECT column_name FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = :table"
            );
            $existing->execute([':table' => $table]);
            $existingColumns = array_map(
                static fn(array $row): string => (string)$row['column_name'],
                $existing->fetchAll()
            );

            foreach ($tableColumns as $column => $definition) {
                if (in_array($column, $existingColumns, true)) {
                    continue;
                }
                try {
                    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                } catch (PDOException $e) {
                    // Another request may have completed the same startup migration.
                    if ($e->getCode() !== '42S21' && !str_contains($e->getMessage(), 'Duplicate column name')) {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Recover referral rows for accounts created before referral binding was enabled.
     * The unique pair constraint keeps this migration safe to run on every request.
     */
    private static function backfillMysqlReferrals(PDO $pdo): void {
        $tables = $pdo->query("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name IN ('users', 'referrals')
        ")->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) !== 2) {
            return;
        }

        $pdo->exec("
            INSERT IGNORE INTO referrals (referrer_id, referred_user_id, referral_code, status, reward_amount, created_at)
            SELECT referrer.id, referred.id, referred.referred_by, 'pending', 0.00, referred.created_at
            FROM users referred
            JOIN users referrer ON UPPER(referrer.referral_code) = UPPER(referred.referred_by)
            LEFT JOIN referrals existing
                ON existing.referrer_id = referrer.id
               AND existing.referred_user_id = referred.id
            WHERE referred.referred_by IS NOT NULL
              AND TRIM(referred.referred_by) <> ''
              AND existing.id IS NULL
        ");
    }

    private static function bootstrapSqlite(PDO $pdo): void {
        $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
        if (!$check) {
            $seedFile = dirname(__DIR__) . '/database/seed.php';
            if (file_exists($seedFile)) {
                require_once $seedFile;
            }
        }

        self::ensureSqliteColumns($pdo);
    }

    private static function getSqlitePath(): string {
        $configuredPath = trim((string)(getenv('DB_SQLITE_PATH') ?: ''));
        if ($configuredPath !== '') {
            return $configuredPath;
        }

        return dirname(__DIR__) . '/storage/database.sqlite';
    }

    private static function ensureSqliteColumns(PDO $pdo): void {
        $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
            id TEXT PRIMARY KEY,
            user_id INTEGER NULL,
            ip_address TEXT NULL,
            user_agent TEXT NULL,
            payload TEXT NOT NULL,
            last_activity INTEGER NOT NULL
        )");

        $tables = [
            'users' => [
                'email_verified' => 'INTEGER DEFAULT 0',
                'email_verification_token' => 'TEXT NULL',
                'email_verification_expires_at' => 'TEXT NULL',
                'email_verified_at' => 'TEXT NULL',
                'password_reset_token' => 'TEXT NULL',
                'password_reset_expires_at' => 'TEXT NULL',
                'registration_ip' => 'TEXT NULL',
                'last_seen_ip' => 'TEXT NULL',
                'device_fingerprint' => 'TEXT NULL',
                'vpn_detected' => 'INTEGER DEFAULT 0',
                'vpn_detected_at' => 'TEXT NULL',
                'suspicious_flags' => 'TEXT DEFAULT ""',
                'blocked_reason' => 'TEXT NULL',
                'updated_at' => 'TEXT DEFAULT (datetime("now"))',
            ],
            'deposits' => [
                'transaction_id' => 'TEXT DEFAULT NULL',
                'proof_receipt_path' => 'TEXT',
                'fee' => 'REAL DEFAULT 0.00',
                'net_amount' => 'REAL DEFAULT 0.00',
                'reference_number' => 'TEXT',
                'proof_document_path' => 'TEXT',
                'reviewed_by' => 'INTEGER',
                'processed_by' => 'INTEGER',
                'processed_at' => 'TEXT',
                'updated_at' => 'TEXT DEFAULT (datetime("now"))',
            ],
            'withdrawals' => [
                'net_amount' => 'REAL NOT NULL DEFAULT 0.00',
                'method' => 'TEXT',
                'account_name' => 'TEXT',
                'account_number' => 'TEXT',
                'bank_name' => 'TEXT',
                'branch_name' => 'TEXT',
                'payout_method' => 'TEXT',
                'account_details' => 'TEXT',
                'processed_by' => 'INTEGER',
                'updated_at' => 'TEXT DEFAULT (datetime("now"))',
            ],
            'wallet_transactions' => [
                'wallet_id' => 'INTEGER DEFAULT 0',
                'fee' => 'REAL DEFAULT 0.00',
                'notes' => 'TEXT',
                'status' => "TEXT DEFAULT 'completed'",
            ],
            'investments' => [
                'accrued_profit' => 'REAL NOT NULL DEFAULT 0.00',
                'total_paid_out' => 'REAL NOT NULL DEFAULT 0.00',
                'duration_days' => 'INTEGER DEFAULT 0',
            ],
            'referrals' => [
                'referral_code' => "TEXT DEFAULT ''",
                'status' => "TEXT DEFAULT 'pending'",
                'reward_amount' => 'REAL DEFAULT 0.00',
                'rewarded_at' => 'TEXT',
            ],
            'kyc_requests' => [
                'document_type' => "TEXT DEFAULT 'citizenship'",
                'id_number' => 'TEXT',
                'full_name' => 'TEXT',
                'father_name' => 'TEXT',
                'mother_name' => 'TEXT',
                'dob' => 'TEXT',
                'gender' => "TEXT DEFAULT 'male'",
                'permanent_address' => 'TEXT',
                'current_address' => 'TEXT',
                'front_document_path' => 'TEXT',
                'back_document_path' => 'TEXT',
                'selfie_path' => 'TEXT',
                'status' => "TEXT DEFAULT 'pending'",
                'rejection_reason' => 'TEXT',
                'reviewed_by' => 'INTEGER',
                'reviewed_at' => 'TEXT',
                'submitted_at' => 'TEXT DEFAULT (datetime("now"))',
                'created_at' => 'TEXT DEFAULT (datetime("now"))',
                'updated_at' => 'TEXT DEFAULT (datetime("now"))',
            ],
        ];

        foreach ($tables as $table => $columns) {
            $existing = $pdo->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
            if (!$existing) {
                continue;
            }

            $existingColumns = [];
            foreach ($existing as $column) {
                $existingColumns[$column['name']] = true;
            }

            foreach ($columns as $column => $type) {
                if (!isset($existingColumns[$column])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$type}");
                    $existingColumns[$column] = true;
                }
            }

            if ($table === 'kyc_requests') {
                if (!isset($existingColumns['dob']) && isset($existingColumns['date_of_birth'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN dob TEXT");
                    $pdo->exec("UPDATE {$table} SET dob = date_of_birth WHERE dob IS NULL AND date_of_birth IS NOT NULL");
                }
                if (!isset($existingColumns['mother_name'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN mother_name TEXT");
                }
                if (!isset($existingColumns['gender'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN gender TEXT DEFAULT 'male'");
                }
                if (!isset($existingColumns['permanent_address'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN permanent_address TEXT");
                }
                if (!isset($existingColumns['current_address'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN current_address TEXT");
                }
                if (!isset($existingColumns['reviewed_by']) && isset($existingColumns['verified_by'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN reviewed_by INTEGER");
                    $pdo->exec("UPDATE {$table} SET reviewed_by = verified_by WHERE reviewed_by IS NULL AND verified_by IS NOT NULL");
                }
                if (!isset($existingColumns['reviewed_at']) && isset($existingColumns['verified_at'])) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN reviewed_at TEXT");
                    $pdo->exec("UPDATE {$table} SET reviewed_at = verified_at WHERE reviewed_at IS NULL AND verified_at IS NOT NULL");
                }
            }

        }
    }

    private static function ensureMysqlSessionTable(PDO $pdo): void {
        $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload LONGTEXT NOT NULL,
            last_activity INT UNSIGNED NOT NULL,
            INDEX idx_sessions_last_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
