<?php
/**
 * CapitalNest Nepal - Master Database Migrator & Seed Runner
 * Supports both MySQL and SQLite transparently.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

use Config\Database;

function runMigrationAndSeed(): void {
    $db = Database::getConnection();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

    echo "Running database setup for driver: {$driver}...\n";

    if ($driver === 'sqlite') {
        // SQLite Schema Creation
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                referral_code TEXT NOT NULL UNIQUE,
                referred_by TEXT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                phone TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                email_verified INTEGER NOT NULL DEFAULT 0,
                email_verification_token TEXT NULL,
                email_verification_expires_at TEXT NULL,
                email_verified_at TEXT NULL,
                password_reset_token TEXT NULL,
                password_reset_expires_at TEXT NULL,
                two_factor_enabled INTEGER NOT NULL DEFAULT 0,
                last_login_at TEXT NULL,
                last_login_ip TEXT NULL,
                registration_ip TEXT NULL,
                last_seen_ip TEXT NULL,
                device_fingerprint TEXT NULL,
                vpn_detected INTEGER NOT NULL DEFAULT 0,
                vpn_detected_at TEXT NULL,
                suspicious_flags TEXT NOT NULL DEFAULT '',
                blocked_reason TEXT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS user_profiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL UNIQUE,
                address TEXT NULL,
                city TEXT NULL,
                province TEXT NULL,
                date_of_birth TEXT NULL,
                avatar_path TEXT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS wallets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL UNIQUE,
                available_balance REAL NOT NULL DEFAULT 0.00,
                invested_balance REAL NOT NULL DEFAULT 0.00,
                pending_withdrawal REAL NOT NULL DEFAULT 0.00,
                total_earnings REAL NOT NULL DEFAULT 0.00,
                total_deposits REAL NOT NULL DEFAULT 0.00,
                total_withdrawals REAL NOT NULL DEFAULT 0.00,
                currency TEXT NOT NULL DEFAULT 'NPR',
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS wallet_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                transaction_ref TEXT NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                wallet_id INTEGER NOT NULL,
                fee REAL NOT NULL DEFAULT 0.00,
                type TEXT NOT NULL,
                amount REAL NOT NULL,
                previous_balance REAL NOT NULL,
                new_balance REAL NOT NULL,
                reference TEXT NULL,
                notes TEXT NULL,
                status TEXT NOT NULL DEFAULT 'completed',
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS investment_plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                badge TEXT NULL,
                description TEXT NULL,
                min_investment REAL NOT NULL,
                max_investment REAL NOT NULL,
                return_rate REAL NOT NULL,
                duration_days INTEGER NOT NULL,
                payout_frequency TEXT NOT NULL DEFAULT 'monthly',
                capital_return INTEGER NOT NULL DEFAULT 1,
                status TEXT NOT NULL DEFAULT 'active',
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS investments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                investment_ref TEXT NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                plan_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                return_rate REAL NOT NULL,
                expected_return REAL NOT NULL,
                accrued_profit REAL NOT NULL DEFAULT 0.00,
                total_paid_out REAL NOT NULL DEFAULT 0.00,
                duration_days INTEGER NOT NULL,
                start_date TEXT NOT NULL,
                end_date TEXT NOT NULL,
                next_payout_date TEXT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS deposits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                deposit_ref TEXT NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                fee REAL NOT NULL DEFAULT 0.00,
                net_amount REAL NOT NULL DEFAULT 0.00,
                transaction_id TEXT NULL,
                payment_method TEXT NOT NULL,
                reference_number TEXT NOT NULL,
                proof_receipt_path TEXT NULL,
                proof_document_path TEXT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                admin_notes TEXT NULL,
                reviewed_by INTEGER NULL,
                processed_by INTEGER NULL,
                processed_at TEXT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS withdrawals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                withdrawal_ref TEXT NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                fee REAL NOT NULL DEFAULT 0.00,
                net_amount REAL NOT NULL,
                payout_method TEXT NOT NULL,
                account_details TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                admin_notes TEXT NULL,
                processed_by INTEGER NULL,
                processed_at TEXT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS kyc_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                document_type TEXT NOT NULL DEFAULT 'citizenship',
                id_number TEXT NOT NULL,
                full_name TEXT NOT NULL,
                father_name TEXT NULL,
                mother_name TEXT NULL,
                dob TEXT NULL,
                gender TEXT NOT NULL DEFAULT 'male',
                permanent_address TEXT NOT NULL,
                current_address TEXT NOT NULL,
                front_document_path TEXT NOT NULL,
                back_document_path TEXT NULL,
                selfie_path TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                rejection_reason TEXT NULL,
                reviewed_by INTEGER NULL,
                reviewed_at TEXT NULL,
                submitted_at TEXT NOT NULL DEFAULT (datetime('now')),
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS referrals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                referrer_id INTEGER NOT NULL,
                referred_user_id INTEGER NOT NULL UNIQUE,
                referral_code TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                reward_amount REAL NOT NULL DEFAULT 0.00,
                rewarded_at TEXT NULL,
                level INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS referral_earnings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                from_user_id INTEGER NOT NULL,
                investment_id INTEGER NOT NULL,
                commission_rate REAL NOT NULL,
                investment_amount REAL NOT NULL,
                commission_amount REAL NOT NULL,
                status TEXT NOT NULL DEFAULT 'credited',
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                title TEXT NOT NULL,
                message TEXT NOT NULL,
                type TEXT NOT NULL DEFAULT 'system',
                is_read INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS support_tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_ref TEXT NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                subject TEXT NOT NULL,
                category TEXT NOT NULL DEFAULT 'account',
                priority TEXT NOT NULL DEFAULT 'medium',
                status TEXT NOT NULL DEFAULT 'open',
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS support_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER NOT NULL,
                sender_type TEXT NOT NULL,
                sender_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'admin',
                status TEXT NOT NULL DEFAULT 'active',
                last_login_at TEXT NULL,
                last_login_ip TEXT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS admin_activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                admin_id INTEGER NOT NULL,
                action TEXT NOT NULL,
                target_type TEXT NOT NULL,
                target_id TEXT NOT NULL,
                details TEXT NULL,
                ip_address TEXT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
            "CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                description TEXT NULL,
                category TEXT NOT NULL DEFAULT 'general',
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )"
        ];

        foreach ($tables as $sql) {
            $db->exec($sql);
        }
    }

    // --- SEED ADMIN USERS FROM ENV ---
    $adminName = getenv('ADMIN_NAME') ?: 'CapitalNest Compliance Officer';
    $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@capitalnest.np';
    $adminPassword = getenv('ADMIN_PASSWORD');

    if (empty($adminPassword)) {
        echo "Admin password is not configured in environment variables. Skipping admin seed.\n";
        return;
    }

    $adminCheck = $db->query("SELECT COUNT(*) FROM admin_users WHERE email = " . $db->quote($adminEmail))->fetchColumn();
    if ((int)$adminCheck === 0) {
        $pwHash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO admin_users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$adminName, $adminEmail, $pwHash, 'super_admin']);
        echo "Created super admin account.\n";
    }

    // --- LIVE STATE: no demo investor/wallet data seeded ---
    // Real users will be created through the registration flow and stored normally.
    // Existing demo balances are cleared during startup to keep admin totals at zero.

    // --- SEED INVESTMENT PLANS ---
    $plansCheck = (int)$db->query("SELECT COUNT(*) FROM investment_plans")->fetchColumn();
    if ($plansCheck === 0) {
        $plans = [
            ['Himalayan Horizon Tier 1', 'himalayan-horizon-tier-1', 'STARTER', 'Conservative high-yield short-term plan anchored in sovereign & low-risk corporate debentures.', 5000.00, 50000.00, 11.50, 30, 'monthly'],
            ['Kathmandu Equity Growth', 'kathmandu-equity-growth', 'POPULAR', 'Balanced portfolio targeting leading commercial banks and renewable hydropower infrastructure in Nepal.', 25000.00, 250000.00, 15.80, 90, 'monthly'],
            ['Everest Sovereign Wealth', 'everest-sovereign-wealth', 'PREMIUM', 'High-alpha investment vehicle focusing on hospitality, cross-border remittance facilities and tech infrastructure.', 100000.00, 1000000.00, 21.00, 180, 'monthly'],
            ['Annapurna Institutional Reserve', 'annapurna-institutional-reserve', 'EXCLUSIVE', 'Private placement fund for accredited HNIs and high-volume corporate treasury yields.', 500000.00, 5000000.00, 26.50, 365, 'monthly']
        ];
        $stmtPlan = $db->prepare("INSERT INTO investment_plans (name, slug, badge, description, min_investment, max_investment, return_rate, duration_days, payout_frequency, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        foreach ($plans as $p) {
            $stmtPlan->execute($p);
        }
        echo "Seeded 4 investment plans.\n";
    }

    // --- SEED SETTINGS ---
    $settingsCheck = (int)$db->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($settingsCheck === 0) {
        $settings = [
            ['company_name', 'CapitalNest Nepal Pvt. Ltd.', 'Entity Name', 'general'],
            ['support_email', 'support@capitalnest.np', 'Contact Email', 'general'],
            ['support_phone', '+977-01-4458921', 'Corporate Line', 'general'],
            ['min_deposit', '500', 'Minimum allowed deposit', 'finance'],
            ['min_withdrawal', '500', 'Minimum allowed withdrawal', 'finance'],
            ['referral_commission_rate', '5.0', 'Direct referral commission percent', 'finance'],
            ['bank_name', 'Nabil Bank Limited', 'Company Bank', 'payment'],
            ['bank_account_name', 'CAPITALNEST NEPAL PVT LTD', 'Account Title', 'payment'],
            ['bank_account_number', '01900175249821', 'Account Number', 'payment'],
            ['bank_branch', 'Kathmandu Corporate Branch', 'Branch Name', 'payment'],
            ['esewa_name', 'CapitalNest Treasury', 'eSewa display name', 'payment'],
            ['esewa_id', '9801234567', 'Official eSewa Gateway ID', 'payment'],
            ['esewa_qr_url', '', 'Official eSewa QR image URL', 'payment'],
            ['khalti_name', 'CapitalNest Treasury', 'Khalti display name', 'payment'],
            ['khalti_id', '9801234567', 'Official Khalti Merchant Key', 'payment'],
            ['khalti_qr_url', '', 'Official Khalti QR image URL', 'payment'],
            ['fonepay_name', 'CapitalNest Treasury', 'FonePay display name', 'payment'],
            ['fonepay_id', '', 'Official FonePay Merchant ID (optional)', 'payment'],
            ['fonepay_qr_url', '', 'Official FonePay QR image URL', 'payment'],
            ['maintenance_mode', '0', 'Maintenance Mode Status', 'system']
        ];
        $stmtSet = $db->prepare("INSERT INTO settings (setting_key, setting_value, description, category) VALUES (?, ?, ?, ?)");
        foreach ($settings as $s) {
            $stmtSet->execute($s);
        }
        echo "Seeded platform settings.\n";
    }

    // --- SEED NOTIFICATIONS ---
    $notifCheck = (int)$db->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    if ($notifCheck === 0) {
        $db->exec("INSERT INTO notifications (title, message, type, is_read) VALUES 
            ('Welcome to CapitalNest Nepal', 'Your institutional wealth management portal is now operational. Please ensure your KYC identity verification is submitted.', 'system', 0),
            ('NRB Monetary Policy Update', 'Interest rates updated across commercial treasury offerings. New returns effective immediately.', 'investment', 0)
        ");
        echo "Seeded welcome notifications.\n";
    }

    echo "Database setup completed successfully.\n";
}

runMigrationAndSeed();
