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

        $connectionType = getenv('DB_CONNECTION') ?: 'mysql';
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: 'capitalnest_db');
        $username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'root');
        $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

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
            } else {
                // Portable SQLite fallback for local verification
                $sqlitePath = dirname(__DIR__) . '/storage/database.sqlite';
                $dsn = "sqlite:{$sqlitePath}";
                self::$instance = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                self::ensureSqliteColumns(self::$instance);
            }
        } catch (PDOException $e) {
            // If MySQL is not locally reachable (e.g. during standalone container builds), fallback gracefully to SQLite
            try {
                $sqliteDir = dirname(__DIR__) . '/storage';
                if (!is_dir($sqliteDir)) {
                    @mkdir($sqliteDir, 0755, true);
                }
                $sqlitePath = $sqliteDir . '/database.sqlite';
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

    private static function ensureSqliteColumns(PDO $pdo): void {
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
}
