-- ==========================================================
-- CapitalNest Nepal - Enterprise FinTech Database Schema
-- Standard: MySQL 8.0+ / MariaDB 10.5+
-- Storage Engine: InnoDB
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- Precision for Monetary Values: DECIMAL(18, 2)
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `admin_activity_logs`;
DROP TABLE IF EXISTS `support_messages`;
DROP TABLE IF EXISTS `support_tickets`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `kyc_requests`;
DROP TABLE IF EXISTS `referral_earnings`;
DROP TABLE IF EXISTS `referrals`;
DROP TABLE IF EXISTS `withdrawals`;
DROP TABLE IF EXISTS `deposits`;
DROP TABLE IF EXISTS `wallet_transactions`;
DROP TABLE IF EXISTS `wallets`;
DROP TABLE IF EXISTS `investments`;
DROP TABLE IF EXISTS `investment_plans`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `user_profiles`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referral_code` VARCHAR(20) NOT NULL,
  `referred_by` VARCHAR(20) NULL DEFAULT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `email_verification_token` VARCHAR(255) NULL DEFAULT NULL,
  `email_verification_expires_at` DATETIME NULL DEFAULT NULL,
  `email_verified_at` DATETIME NULL DEFAULT NULL,
  `password_reset_token` VARCHAR(255) NULL DEFAULT NULL,
  `password_reset_expires_at` DATETIME NULL DEFAULT NULL,
  `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `last_login_ip` VARCHAR(45) NULL DEFAULT NULL,
  `registration_ip` VARCHAR(45) NULL DEFAULT NULL,
  `last_seen_ip` VARCHAR(45) NULL DEFAULT NULL,
  `device_fingerprint` VARCHAR(128) NULL DEFAULT NULL,
  `vpn_detected` TINYINT(1) NOT NULL DEFAULT 0,
  `vpn_detected_at` DATETIME NULL DEFAULT NULL,
  `suspicious_flags` VARCHAR(255) NULL DEFAULT '',
  `blocked_reason` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  UNIQUE KEY `uk_users_referral_code` (`referral_code`),
  INDEX `idx_users_status` (`status`),
  INDEX `idx_users_referred_by` (`referred_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USER PROFILES TABLE
CREATE TABLE `user_profiles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `address` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `district` VARCHAR(100) NULL DEFAULT 'Kathmandu',
  `province` VARCHAR(100) NULL DEFAULT 'Bagmati',
  `country` VARCHAR(80) NOT NULL DEFAULT 'Nepal',
  `date_of_birth` DATE NULL DEFAULT NULL,
  `occupation` VARCHAR(120) NULL DEFAULT NULL,
  `avatar_url` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_profiles_user_id` (`user_id`),
  CONSTRAINT `fk_user_profiles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SESSIONS TABLE
CREATE TABLE `sessions` (
  `id` VARCHAR(128) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_sessions_user_id` (`user_id`),
  INDEX `idx_sessions_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. INVESTMENT PLANS TABLE
CREATE TABLE `investment_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `badge` VARCHAR(50) NULL DEFAULT 'POPULAR',
  `description` TEXT NOT NULL,
  `min_investment` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `max_investment` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `return_rate` DECIMAL(6, 2) NOT NULL COMMENT 'Percentage return',
  `duration_days` INT NOT NULL COMMENT 'Maturity duration in days',
  `payout_frequency` ENUM('daily', 'weekly', 'monthly', 'maturity') NOT NULL DEFAULT 'monthly',
  `capital_return` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_investment_plans_slug` (`slug`),
  INDEX `idx_investment_plans_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. INVESTMENTS TABLE
CREATE TABLE `investments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `investment_ref` VARCHAR(32) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(18, 2) NOT NULL,
  `return_rate` DECIMAL(6, 2) NOT NULL,
  `expected_return` DECIMAL(18, 2) NOT NULL,
  `accrued_profit` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `next_payout_date` DATETIME NULL DEFAULT NULL,
  `status` ENUM('pending', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_investments_ref` (`investment_ref`),
  INDEX `idx_investments_user_id` (`user_id`),
  INDEX `idx_investments_plan_id` (`plan_id`),
  INDEX `idx_investments_status` (`status`),
  INDEX `idx_investments_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_investments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_investments_plan_id` FOREIGN KEY (`plan_id`) REFERENCES `investment_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. WALLETS TABLE (Central Balance Engine)
CREATE TABLE `wallets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `available_balance` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `invested_balance` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `total_earnings` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `total_deposits` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `total_withdrawals` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `currency` VARCHAR(10) NOT NULL DEFAULT 'NPR',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wallets_user_id` (`user_id`),
  CONSTRAINT `fk_wallets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. WALLET TRANSACTIONS TABLE (Audit Trail)
CREATE TABLE `wallet_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_ref` VARCHAR(36) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('deposit', 'withdrawal', 'investment', 'profit', 'referral', 'adjustment') NOT NULL,
  `amount` DECIMAL(18, 2) NOT NULL,
  `fee` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `previous_balance` DECIMAL(18, 2) NOT NULL,
  `new_balance` DECIMAL(18, 2) NOT NULL,
  `reference` VARCHAR(100) NOT NULL COMMENT 'External or internal correlation ID',
  `notes` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('completed', 'pending', 'failed') NOT NULL DEFAULT 'completed',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wallet_tx_ref` (`transaction_ref`),
  INDEX `idx_wallet_tx_user_id` (`user_id`),
  INDEX `idx_wallet_tx_type` (`type`),
  INDEX `idx_wallet_tx_created_at` (`created_at`),
  CONSTRAINT `fk_wallet_tx_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. DEPOSITS TABLE
CREATE TABLE `deposits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deposit_ref` VARCHAR(36) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(18, 2) NOT NULL,
  `fee` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `net_amount` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `transaction_id` VARCHAR(100) NULL DEFAULT NULL,
  `payment_method` VARCHAR(50) NOT NULL COMMENT 'eSewa, Khalti, Bank Transfer, ConnectIPS',
  `reference_number` VARCHAR(100) NOT NULL COMMENT 'Transaction ID/slip number',
  `proof_document_path` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` TEXT NULL DEFAULT NULL,
  `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `processed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `processed_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_deposits_ref` (`deposit_ref`),
  INDEX `idx_deposits_user_id` (`user_id`),
  INDEX `idx_deposits_status` (`status`),
  INDEX `idx_deposits_created_at` (`created_at`),
  CONSTRAINT `fk_deposits_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. WITHDRAWALS TABLE
CREATE TABLE `withdrawals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `withdrawal_ref` VARCHAR(36) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(18, 2) NOT NULL,
  `fee` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `method` VARCHAR(50) NOT NULL COMMENT 'Bank Transfer, eSewa, Khalti',
  `account_name` VARCHAR(120) NOT NULL,
  `account_number` VARCHAR(80) NOT NULL,
  `bank_name` VARCHAR(120) NULL DEFAULT NULL,
  `branch_name` VARCHAR(100) NULL DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` TEXT NULL DEFAULT NULL,
  `processed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_withdrawals_ref` (`withdrawal_ref`),
  INDEX `idx_withdrawals_user_id` (`user_id`),
  INDEX `idx_withdrawals_status` (`status`),
  INDEX `idx_withdrawals_created_at` (`created_at`),
  CONSTRAINT `fk_withdrawals_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. REFERRALS TABLE
CREATE TABLE `referrals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` BIGINT UNSIGNED NOT NULL,
  `referred_user_id` BIGINT UNSIGNED NOT NULL,
  `referral_code` VARCHAR(20) NOT NULL,
  `status` ENUM('pending', 'successful', 'rewarded', 'cancelled') NOT NULL DEFAULT 'pending',
  `reward_amount` DECIMAL(18, 2) NOT NULL DEFAULT '0.00',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rewarded_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_referrals_pair` (`referrer_id`, `referred_user_id`),
  INDEX `idx_referrals_code` (`referral_code`),
  INDEX `idx_referrals_status` (`status`),
  CONSTRAINT `fk_referrals_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referrals_referred` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. REFERRAL EARNINGS TABLE
CREATE TABLE `referral_earnings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referral_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The beneficiary referrer',
  `from_user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The referee who invested',
  `investment_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `amount` DECIMAL(18, 2) NOT NULL,
  `commission_rate` DECIMAL(5, 2) NOT NULL DEFAULT '5.00',
  `status` ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'paid',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ref_earnings_user_id` (`user_id`),
  INDEX `idx_ref_earnings_referral_id` (`referral_id`),
  CONSTRAINT `fk_ref_earnings_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ref_earnings_referral` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. KYC REQUESTS TABLE
CREATE TABLE `kyc_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `document_type` ENUM('citizenship', 'national_id', 'passport', 'driving_license') NOT NULL,
  `id_number` VARCHAR(80) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `father_name` VARCHAR(150) NULL DEFAULT NULL,
  `mother_name` VARCHAR(150) NULL DEFAULT NULL,
  `dob` DATE NOT NULL,
  `gender` ENUM('male', 'female', 'other') NOT NULL DEFAULT 'male',
  `permanent_address` TEXT NOT NULL,
  `current_address` TEXT NOT NULL,
  `front_document_path` VARCHAR(255) NOT NULL,
  `back_document_path` VARCHAR(255) NULL DEFAULT NULL,
  `selfie_path` VARCHAR(255) NOT NULL,
  `status` ENUM('not_submitted', 'pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT NULL DEFAULT NULL,
  `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_kyc_user_id` (`user_id`),
  INDEX `idx_kyc_status` (`status`),
  CONSTRAINT `fk_kyc_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. NOTIFICATIONS TABLE
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('system', 'transaction', 'investment', 'kyc', 'security', 'referral') NOT NULL DEFAULT 'system',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `action_url` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notifications_user_unread` (`user_id`, `is_read`),
  INDEX `idx_notifications_created_at` (`created_at`),
  CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. SUPPORT TICKETS TABLE
CREATE TABLE `support_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_ref` VARCHAR(32) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `category` ENUM('deposit', 'withdrawal', 'investment', 'kyc', 'account', 'technical', 'other') NOT NULL DEFAULT 'account',
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `status` ENUM('open', 'pending', 'resolved', 'closed') NOT NULL DEFAULT 'open',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tickets_ref` (`ticket_ref`),
  INDEX `idx_tickets_user_id` (`user_id`),
  INDEX `idx_tickets_status` (`status`),
  CONSTRAINT `fk_tickets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. SUPPORT MESSAGES TABLE
CREATE TABLE `support_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `sender_type` ENUM('user', 'admin') NOT NULL,
  `sender_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `attachment_path` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_support_messages_ticket` (`ticket_id`),
  CONSTRAINT `fk_support_messages_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. ADMIN USERS TABLE
CREATE TABLE `admin_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'finance', 'compliance', 'support') NOT NULL DEFAULT 'admin',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `last_login_ip` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_email` (`email`),
  INDEX `idx_admin_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. ADMIN ACTIVITY LOGS TABLE
CREATE TABLE `admin_activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(60) NOT NULL,
  `target_id` VARCHAR(60) NOT NULL,
  `details` JSON NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_activity_admin_id` (`admin_id`),
  INDEX `idx_activity_created_at` (`created_at`),
  CONSTRAINT `fk_activity_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. SETTINGS TABLE
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'general',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED INITIAL DATA (Plans, System Settings & Default Admin)
-- ==========================================================

-- Investment Plans for Nepal Market (NRs.)
INSERT INTO `investment_plans` (`name`, `slug`, `badge`, `description`, `min_investment`, `max_investment`, `return_rate`, `duration_days`, `payout_frequency`, `capital_return`, `status`) VALUES
('Himalayan Horizon Tier 1', 'himalayan-horizon-tier-1', 'STARTER', 'Conservative high-yield short-term plan anchored in sovereign & low-risk corporate debentures in Nepal.', 5000.00, 50000.00, 11.50, 30, 'monthly', 1, 'active'),
('Kathmandu Equity Growth', 'kathmandu-equity-growth', 'POPULAR', 'Balanced portfolio targeting leading commercial banks and renewable hydropower infrastructure in Nepal.', 25000.00, 250000.00, 15.80, 90, 'monthly', 1, 'active'),
('Everest Sovereign Wealth', 'everest-sovereign-wealth', 'PREMIUM', 'High-alpha investment vehicle focusing on hospitality, cross-border remittance facilities and tech infrastructure.', 100000.00, 1000000.00, 21.00, 180, 'monthly', 1, 'active'),
('Annapurna Institutional Reserve', 'annapurna-institutional-reserve', 'EXCLUSIVE', 'Private placement fund for accredited HNIs and high-volume corporate treasury yields.', 500000.00, 5000000.00, 26.50, 365, 'monthly', 1, 'active');

-- System Configuration Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`, `category`) VALUES
('site_name', 'CapitalNest Nepal', 'Official name of the platform', 'general'),
('site_tagline', 'Premier Wealth & FinTech Investment Management Nepal', 'Platform subtitle', 'general'),
('currency_code', 'NPR', 'Default operating currency', 'finance'),
('currency_symbol', 'रू', 'Display currency symbol', 'finance'),
('min_deposit', '1000.00', 'Minimum allowed deposit in NPR', 'finance'),
('max_deposit', '2000000.00', 'Maximum allowed deposit in NPR', 'finance'),
('min_withdrawal', '500.00', 'Minimum allowed withdrawal in NPR', 'finance'),
('max_withdrawal', '500000.00', 'Maximum allowed withdrawal in NPR per transaction', 'finance'),
('withdrawal_fee_percent', '1.00', 'Standard processing fee on withdrawals', 'finance'),
('referral_commission_rate', '5.00', 'Referral commission % credited on referee initial investment', 'finance'),
('esewa_merchant_id', 'CAPITALNEST_NP', 'Official eSewa Gateway ID', 'payment'),
('khalti_public_key', 'test_public_key_capitalnest', 'Official Khalti Merchant Key', 'payment'),
('company_bank_name', 'Nabil Bank Ltd.', 'Official company deposit bank', 'payment'),
('company_bank_account_name', 'CapitalNest Nepal Investment Pvt. Ltd.', 'Company account title', 'payment'),
('company_bank_account_no', '01201017500392', 'Company bank account number', 'payment'),
('company_bank_branch', 'Durbarmarg, Kathmandu', 'Company bank branch', 'payment');

-- Super admin credentials are loaded from environment variables at runtime.
-- Do not hardcode email or password values in the SQL schema.
-- Example env values:
-- ADMIN_NAME="CapitalNest Compliance Officer"
-- ADMIN_EMAIL="admin@capitalnest.np"
-- ADMIN_PASSWORD="your-secure-password"
