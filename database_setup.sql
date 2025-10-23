-- Manual Database Setup for Wallet Plugin
-- Run this SQL if the plugin installation didn't create the tables

-- ============================================
-- IMPORTANT: Replace 'rise_' with your actual database prefix if different
-- ============================================

-- Create wallet table
CREATE TABLE IF NOT EXISTS `rise_wallet` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
    `currency` varchar(10) NOT NULL DEFAULT 'USD',
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- Create wallet transactions table
CREATE TABLE IF NOT EXISTS `rise_wallet_transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `wallet_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `transaction_type` varchar(50) NOT NULL COMMENT 'credit, debit',
    `amount` decimal(20,2) NOT NULL,
    `currency` varchar(10) NOT NULL DEFAULT 'USD',
    `reference_type` varchar(50) NULL COMMENT 'invoice, order, manual, etc',
    `reference_id` int(11) NULL,
    `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
    `balance_before` decimal(20,2) NOT NULL,
    `balance_after` decimal(20,2) NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` datetime NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `wallet_id` (`wallet_id`),
    KEY `user_id` (`user_id`),
    KEY `reference_type_id` (`reference_type`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- Create wallet settings table
CREATE TABLE IF NOT EXISTS `rise_wallet_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_name` varchar(100) NOT NULL,
    `setting_value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- Insert default wallet settings
INSERT INTO `rise_wallet_settings` (`setting_name`, `setting_value`, `deleted`) VALUES
    ('wallet_enabled', '1', 0),
    ('minimum_balance', '0.00', 0),
    ('allow_negative_balance', '0', 0),
    ('auto_create_wallet', '1', 0),
    ('wallet_currency', 'USD', 0)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Add payment method for wallet
INSERT INTO `rise_payment_methods` (`title`, `type`, `description`, `online_payable`, `available_on_invoice`, `minimum_payment_amount`, `settings`, `deleted`) 
VALUES ('Wallet Payment', 'wallet_payment', 'Pay using your wallet balance', 1, 1, 0, '', 0)
ON DUPLICATE KEY UPDATE deleted=0;

-- Add notification settings for wallet
INSERT INTO `rise_notification_settings` (`event`, `category`, `enable_email`, `enable_web`, `notify_to_team`, `notify_to_team_members`, `notify_to_terms`, `deleted`) VALUES
    ('wallet_credited', 'wallet', 1, 1, '', '', '', 0),
    ('wallet_debited', 'wallet', 1, 1, '', '', '', 0),
    ('wallet_low_balance', 'wallet', 1, 1, '', '', '', 0)
ON DUPLICATE KEY UPDATE deleted=0;

-- Add wallet notification field to notifications table (if column doesn't exist)
-- Check first if column exists, then add
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_NAME = 'rise_notifications'
   AND TABLE_SCHEMA = DATABASE()
   AND COLUMN_NAME = 'plugin_wallet_transaction_id') > 0,
  "SELECT 1",
  "ALTER TABLE `rise_notifications` ADD `plugin_wallet_transaction_id` INT(11) NULL AFTER `deleted`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verify tables were created
SELECT 'Wallet tables created successfully!' AS status;
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME LIKE 'rise_wallet%';
