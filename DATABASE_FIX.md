# Database Error Fix - Table Doesn't Exist

## The Error

```
Table 'rise.rise_rise_wallet' doesn't exist
```

OR

```
Table 'rise.rise_wallet' doesn't exist
```

## Root Cause

The database tables weren't created during plugin installation. This can happen if:
1. Installation hook didn't run properly
2. Database user lacks CREATE TABLE permissions
3. Database connection timeout
4. Plugin was installed before the fix was applied

## Quick Fix - Option 1: Run SQL Manually (Easiest) ✅

### Step 1: Access Your Database

**Option A - phpMyAdmin:**
1. Open phpMyAdmin
2. Select your RISE database (usually named `rise`)
3. Click "SQL" tab

**Option B - Command Line:**
```bash
mysql -u your_username -p rise
```

### Step 2: Run the Setup SQL

1. Open file: `database_setup.sql` (included in plugin folder)
2. Copy all the SQL
3. Paste into phpMyAdmin SQL tab or MySQL command line
4. Click "Go" or press Enter

### Step 3: Verify Tables Created

Run this query:
```sql
SHOW TABLES LIKE 'rise_wallet%';
```

Should show 3 tables:
- `rise_wallet`
- `rise_wallet_transactions`
- `rise_wallet_settings`

### Step 4: Test

1. Go to RISE
2. Click "Wallet" menu
3. Should work now! ✓

## Quick Fix - Option 2: Reinstall Plugin

1. **Deactivate** plugin (Settings > Plugins)
2. **Uninstall** plugin
3. **Delete** plugin files
4. **Upload** fresh copy from this package (has fixes)
5. **Install** again
6. **Activate**
7. Tables should be created automatically

## Manual Database Setup (If SQL File Not Available)

Copy and run this SQL in your database:

```sql
-- Replace 'rise_' with your actual prefix if different

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

CREATE TABLE IF NOT EXISTS `rise_wallet_transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `wallet_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `transaction_type` varchar(50) NOT NULL,
    `amount` decimal(20,2) NOT NULL,
    `currency` varchar(10) NOT NULL DEFAULT 'USD',
    `reference_type` varchar(50) NULL,
    `reference_id` int(11) NULL,
    `description` text,
    `balance_before` decimal(20,2) NOT NULL,
    `balance_after` decimal(20,2) NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` datetime NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `wallet_id` (`wallet_id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `rise_wallet_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_name` varchar(100) NOT NULL,
    `setting_value` text NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `rise_wallet_settings` (`setting_name`, `setting_value`, `deleted`) VALUES
    ('wallet_enabled', '1', 0),
    ('minimum_balance', '0.00', 0),
    ('allow_negative_balance', '0', 0),
    ('auto_create_wallet', '1', 0),
    ('wallet_currency', 'USD', 0);

INSERT INTO `rise_payment_methods` (`title`, `type`, `description`, `online_payable`, `available_on_invoice`, `minimum_payment_amount`, `settings`, `deleted`) 
VALUES ('Wallet Payment', 'wallet_payment', 'Pay using your wallet balance', 1, 1, 0, '', 0);

INSERT INTO `rise_notification_settings` (`event`, `category`, `enable_email`, `enable_web`, `deleted`) VALUES
    ('wallet_credited', 'wallet', 1, 1, 0),
    ('wallet_debited', 'wallet', 1, 1, 0);
```

## Verification Checklist

After running the SQL, verify:

✅ **Tables exist:**
```sql
SHOW TABLES LIKE 'rise_wallet%';
```

✅ **Wallet settings populated:**
```sql
SELECT * FROM rise_wallet_settings;
```
Should show 5 rows

✅ **Payment method exists:**
```sql
SELECT * FROM rise_payment_methods WHERE type = 'wallet_payment';
```
Should show 1 row

✅ **Notification settings exist:**
```sql
SELECT * FROM rise_notification_settings WHERE category = 'wallet';
```
Should show 2-3 rows

## Common Issues

### Issue 1: Permission Denied Creating Tables

**Error:**
```
Access denied for user 'dbuser'@'localhost' to database 'rise'
```

**Solution:**
Grant CREATE permission to your database user:
```sql
GRANT CREATE ON rise.* TO 'your_db_user'@'localhost';
FLUSH PRIVILEGES;
```

### Issue 2: Wrong Database Prefix

**Error:**
```
Table 'rise_rise_wallet' doesn't exist
```

**Solution:**
Check your actual database prefix in RISE config and update the SQL:

1. Check: `app/Config/Database.php`
2. Find: `DBPrefix` value
3. Replace `rise_` in SQL with your actual prefix

### Issue 3: Tables Already Exist But Wrong Name

**Solution:**
Drop incorrect tables and recreate:
```sql
DROP TABLE IF EXISTS rise_rise_wallet;
DROP TABLE IF EXISTS rise_rise_wallet_transactions;
DROP TABLE IF EXISTS rise_rise_wallet_settings;
```
Then run the correct SQL above.

### Issue 4: SQL Syntax Error

**Solution:**
- Make sure you're using MySQL 5.7+
- Run queries one at a time if batch fails
- Check for trailing semicolons

## Understanding the Table Names

RISE uses a database prefix (default: `rise_`). The correct table names should be:

- ✅ `rise_wallet` (NOT `rise_rise_wallet`)
- ✅ `rise_wallet_transactions` (NOT `rise_rise_wallet_transactions`)
- ✅ `rise_wallet_settings` (NOT `rise_rise_wallet_settings`)

The updated plugin now correctly handles the prefix to avoid duplication.

## After Fixing Database

Once tables are created:

1. ✅ Go to RISE dashboard
2. ✅ Click "Wallet" menu
3. ✅ Should see wallet page (might show empty wallet)
4. ✅ Click "Load Funds"
5. ✅ Add test amount (e.g., 100)
6. ✅ Verify balance updates

## Prevention

To avoid this issue in the future:

1. ✅ Use the **updated plugin** from this package (has database fixes)
2. ✅ Ensure database user has CREATE permissions
3. ✅ Check RISE logs during installation
4. ✅ Verify tables after plugin installation

## Database Backup

Before making any changes, backup your database:

```bash
# Command line backup
mysqldump -u username -p rise > rise_backup.sql

# Restore if needed
mysql -u username -p rise < rise_backup.sql
```

Or use phpMyAdmin: Export tab > Go

## Files Included

- **database_setup.sql** - Complete SQL to create all tables
- Located in: `Wallet_Plugin/database_setup.sql`

## Still Having Issues?

1. **Check database connection:**
   - Verify RISE can connect to database
   - Test with other RISE features

2. **Check database user permissions:**
   ```sql
   SHOW GRANTS FOR 'your_db_user'@'localhost';
   ```

3. **Check RISE error logs:**
   - Location: `app/logs/`
   - Look for database errors

4. **Try different approach:**
   - Use phpMyAdmin instead of command line
   - Or vice versa

5. **Contact support:**
   - Provide error log details
   - Include database prefix being used

## Summary

**Problem:** Database tables not created

**Solution 1:** Run `database_setup.sql` manually ✅

**Solution 2:** Reinstall with updated plugin ✅

**Result:** Wallet should work after tables exist

---

**The updated plugin in this package has database prefix fixes applied!**

Tables should be created correctly on installation now.
