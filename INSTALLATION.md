# Wallet Plugin - Installation Guide

## Quick Installation Steps

### Method 1: Direct Upload (Recommended)

1. **Locate Your RISE Installation**
   - Find your RISE installation directory on your server
   - Navigate to the `plugins` folder (usually `/public_html/plugins` or `/app/Plugins`)

2. **Upload Plugin Files**
   - Upload the entire `Wallet_Plugin` folder to the plugins directory
   - Ensure all files maintain their directory structure

3. **Set Permissions**
   ```bash
   chmod -R 755 Wallet_Plugin
   ```

4. **Access RISE Admin Panel**
   - Log in to RISE as an administrator
   - Go to Settings > Plugins

5. **Install the Plugin**
   - Find "Wallet Payment System" in the plugins list
   - Click "Install" button
   - If prompted for purchase code, enter: `111111-222222` (for testing)
   - Wait for installation to complete (database tables will be created)

6. **Activate the Plugin**
   - After successful installation, click "Activate"
   - Plugin is now ready to use!

### Method 2: ZIP Upload

1. **Create ZIP File**
   - Compress the `Wallet_Plugin` folder into a ZIP file
   - Name it: `Wallet_Plugin.zip`

2. **Upload via RISE Interface**
   - Log in to RISE as admin
   - Go to Settings > Plugins
   - Click "Install from File" or "Upload Plugin"
   - Select the `Wallet_Plugin.zip` file
   - Click Upload

3. **Install and Activate**
   - System will extract and verify the plugin
   - Click "Install" when prompted
   - Enter purchase code if required
   - Click "Activate" to enable the plugin

## Post-Installation Configuration

### Step 1: Configure Basic Settings
1. Go to Settings > Setup > Wallet Settings
2. Configure these options:
   - ✓ Enable Wallet: Check this box
   - ✓ Auto Create Wallet: Enable for automatic wallet creation
   - Currency: Set to your default currency (e.g., USD)
   - Minimum Balance: Set to 0.00 (or your preference)

### Step 2: Configure Payment Method
1. Go to Settings > Setup > Payment Methods
2. Find "Wallet Payment"
3. Click Edit and configure:
   - Pay Button Text: "Pay from Wallet" (or customize)
   - Description: "Use your wallet balance to make this payment"
4. Ensure "Available on Invoice" is enabled

### Step 3: Set Up Notifications
1. Go to Settings > Notifications
2. Find "Wallet" category
3. Enable notifications for:
   - Wallet Credited
   - Wallet Debited
4. Choose notification delivery method (Email, Web, or Both)

### Step 4: Test the Installation
1. **Create a Test Wallet**
   - Navigate to Wallet from the menu
   - System should auto-create a wallet for you

2. **Load Funds**
   - Click "Load Funds" button
   - Enter amount: 100.00
   - Add description: "Test funds"
   - Submit

3. **Verify Balance**
   - Check that balance updated correctly
   - View transaction in transaction history

4. **Test Payment (Optional)**
   - Create a test invoice
   - Try paying with wallet
   - Verify payment is recorded

## Verification Checklist

After installation, verify these items:

- [ ] Plugin appears in Settings > Plugins as "Active"
- [ ] "Wallet" menu item appears in left sidebar
- [ ] Wallet balance widget shows on dashboard
- [ ] Can access Wallet Settings from Settings menu
- [ ] Database tables created successfully:
  - `rise_wallet`
  - `rise_wallet_transactions`
  - `rise_wallet_settings`
- [ ] Payment method "Wallet Payment" exists in payment methods
- [ ] Can load funds successfully
- [ ] Transaction history displays correctly
- [ ] Notifications work for wallet events

## Database Verification

Run these queries to verify installation:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'rise_wallet%';

-- Verify wallet settings
SELECT * FROM rise_wallet_settings;

-- Check payment method
SELECT * FROM rise_payment_methods WHERE type = 'wallet_payment';

-- Verify notification settings
SELECT * FROM rise_notification_settings WHERE category = 'wallet';
```

## Common Installation Issues

### Issue 1: Plugin Not Appearing
**Solution:**
- Check file permissions (should be 755)
- Verify plugin folder name is exactly `Wallet_Plugin`
- Ensure `index.php` exists in plugin root

### Issue 2: Database Tables Not Created
**Solution:**
- Check database user has CREATE TABLE permissions
- Review PHP error logs for SQL errors
- Manually run SQL from installation hook if needed

### Issue 3: Menu Items Not Showing
**Solution:**
- Clear browser cache
- Log out and log back in
- Verify plugin is activated (not just installed)
- Check user permissions

### Issue 4: Payment Method Not Available
**Solution:**
- Go to Settings > Payment Methods
- Find "Wallet Payment"
- Ensure it's not deleted (deleted = 0)
- Check "Available on Invoice" is enabled

## Manual Database Installation

If automatic installation fails, run these SQL commands manually:

```sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create transactions table
CREATE TABLE IF NOT EXISTS `rise_wallet_transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `wallet_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `transaction_type` varchar(50) NOT NULL,
    `amount` decimal(20,2) NOT NULL,
    `currency` varchar(10) NOT NULL DEFAULT 'USD',
    `reference_type` varchar(50) NULL,
    `reference_id` int(11) NULL,
    `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
    `balance_before` decimal(20,2) NOT NULL,
    `balance_after` decimal(20,2) NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` datetime NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `wallet_id` (`wallet_id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create settings table
CREATE TABLE IF NOT EXISTS `rise_wallet_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_name` varchar(100) NOT NULL,
    `setting_value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Insert default settings
INSERT INTO `rise_wallet_settings` (`setting_name`, `setting_value`, `deleted`) VALUES
    ('wallet_enabled', '1', 0),
    ('minimum_balance', '0.00', 0),
    ('allow_negative_balance', '0', 0),
    ('auto_create_wallet', '1', 0),
    ('wallet_currency', 'USD', 0);

-- Add payment method
INSERT INTO `rise_payment_methods` (`title`, `type`, `description`, `online_payable`, `available_on_invoice`, `minimum_payment_amount`, `settings`, `deleted`) VALUES
    ('Wallet Payment', 'wallet_payment', 'Pay using your wallet balance', 1, 1, 0, '', 0);

-- Add notifications
INSERT INTO `rise_notification_settings` (`event`, `category`, `enable_email`, `enable_web`, `deleted`) VALUES
    ('wallet_credited', 'wallet', 1, 1, 0),
    ('wallet_debited', 'wallet', 1, 1, 0);
```

## Uninstallation

To uninstall the plugin:

1. **Deactivate Plugin**
   - Go to Settings > Plugins
   - Click "Deactivate" for Wallet Payment System

2. **Uninstall Plugin**
   - Click "Uninstall"
   - Confirm the action

3. **Remove Files (Optional)**
   - Delete the `Wallet_Plugin` folder from plugins directory

4. **Clean Database (Optional)**
   - Tables are preserved by default
   - To remove data, run:
   ```sql
   DROP TABLE IF EXISTS rise_wallet;
   DROP TABLE IF EXISTS rise_wallet_transactions;
   DROP TABLE IF EXISTS rise_wallet_settings;
   ```

## Support

If you encounter issues during installation:
- Check RISE error logs: `/app/logs/`
- Check PHP error logs
- Contact support: support@yourwebsite.com
- Visit documentation: https://yourwebsite.com/docs

## System Requirements

Before installation, ensure:
- RISE CRM 2.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Write permissions on plugins directory
- Database CREATE TABLE permissions
