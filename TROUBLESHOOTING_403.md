# Wallet Plugin - 403 Forbidden Error Fix

## Problem
You're seeing "403 Forbidden - You don't have permission to access this module" when trying to access the wallet plugin.

## Quick Fix (Choose One)

### Solution 1: Update Controller Permissions (Recommended)

Replace the `__construct` method in the controller file:

**File**: `Wallet_Plugin/Controllers/Wallet_Plugin.php`

**Replace this:**
```php
public function __construct() {
    parent::__construct();
    $this->access_only_allowed_members();
}
```

**With this:**
```php
public function __construct() {
    parent::__construct();
    // Allow access to team members and clients
    $this->access_only_team_members_or_client_contact();
}

// Helper to check if user can access wallet
private function _can_access_wallet() {
    return $this->login_user->user_type === "staff" || $this->login_user->user_type === "client";
}
```

Then add this check at the beginning of the `index()` method:
```php
public function index() {
    // Check if user can access wallet
    if (!$this->_can_access_wallet()) {
        show_404();
    }
    
    // ... rest of the code
}
```

### Solution 2: Add Permission Settings

If you want granular control, add a permission setting:

1. **Add to database** (run this SQL):
```sql
-- Add wallet permission to roles table structure
-- Note: You may need to adjust based on your RISE version
```

2. **Check permissions in controller**:
```php
public function index() {
    // Check for custom permission
    if (!get_setting("module_wallet")) {
        show_404();
    }
    
    // ... rest of code
}
```

### Solution 3: Temporarily Allow All Users (Testing Only)

For **TESTING PURPOSES ONLY**, you can temporarily allow all logged-in users:

```php
public function __construct() {
    parent::__construct();
    // Temporarily allow all logged-in users (TESTING ONLY)
    $this->login_user->id or redirect('signin');
}
```

**⚠️ WARNING**: Don't use Solution 3 in production!

## Detailed Explanation

### Why This Happens

The `access_only_allowed_members()` method in RISE checks if the user has permission to access the module. By default, custom plugins may not have permissions set up.

### Permission Methods in RISE

Here are the available permission methods you can use:

1. **access_only_team_members()** - Only staff can access
2. **access_only_clients()** - Only clients can access
3. **access_only_team_members_or_client_contact()** - Staff OR clients
4. **access_only_admin()** - Only administrators
5. **access_only_admin_or_settings_admin()** - Admin or settings admin
6. **access_only_allowed_members()** - Check specific module permissions (requires setup)

### Best Practice Solution

For the wallet plugin, use a **simple permission check** because:
- Both staff and clients need wallet access
- No additional permission setup required
- Clear and straightforward logic
- Avoids parameter requirement issues

## Step-by-Step Fix

### Step 1: Update the Controller

1. Open file: `plugins/Wallet_Plugin/Controllers/Wallet_Plugin.php`

2. Find the `__construct` method (around line 10)

3. Replace it with:
```php
public function __construct() {
    parent::__construct();
    // Check if user is logged in and is either staff or client
    if (!$this->login_user->id) {
        redirect('signin');
    }
    
    // Only allow staff and client user types
    if (!in_array($this->login_user->user_type, array("staff", "client"))) {
        show_404();
    }
}
```

4. Save the file

### Step 2: Clear Cache

1. Log out of RISE
2. Clear browser cache (Ctrl+Shift+Delete)
3. Log back in
4. Try accessing Wallet again

### Step 3: Verify Database

Check if tables were created:
```sql
SHOW TABLES LIKE 'rise_wallet%';
```

Should return 3 tables:
- rise_wallet
- rise_wallet_transactions
- rise_wallet_settings

### Step 4: Check Plugin Status

1. Go to Settings > Plugins
2. Verify "Wallet Payment System" is:
   - ✓ Installed
   - ✓ Activated (not just installed)

## Alternative Fixes

### If Still Getting 403

**Check 1: User Type**
Make sure you're logged in as either:
- Staff member
- Client contact

**Check 2: Routes**
Verify routes file exists:
`plugins/Wallet_Plugin/Config/Routes.php`

**Check 3: Plugin Active**
```sql
-- Check plugin status in database
SELECT * FROM rise_plugins WHERE name = 'Wallet_Plugin';
```

Should show status as 'active'

**Check 4: File Permissions**
```bash
# Set correct permissions
chmod -R 755 plugins/Wallet_Plugin/
chown -R www-data:www-data plugins/Wallet_Plugin/
```

### Debug Mode

Enable debug mode to see detailed error:

1. Edit `app/Config/Boot/development.php`
2. Set: `ini_set('display_errors', '1');`
3. Try accessing wallet again
4. Check error message

## Updated Plugin Files

I've created an **updated version** with fixed permissions. Here's what changed:

### Changes Made:
1. ✓ Changed `access_only_allowed_members()` to `access_only_team_members_or_client_contact()`
2. ✓ Added `_can_access_wallet()` helper method
3. ✓ Added permission checks in sensitive methods
4. ✓ Improved error handling

### Files Updated:
- `Controllers/Wallet_Plugin.php` - Permission fixes

## Testing After Fix

After applying the fix:

1. **Test as Staff Member**
   - Login as staff
   - Click "Wallet" menu
   - Should see wallet dashboard

2. **Test as Client**
   - Login as client
   - Click "Wallet" menu
   - Should see wallet dashboard

3. **Test Loading Funds**
   - Click "Load Funds"
   - Enter amount: 100
   - Submit
   - Balance should update

## Prevention

To avoid similar issues in future plugins:

1. Always use appropriate permission method
2. Test with different user types
3. Add permission checks in sensitive methods
4. Use RISE's built-in permission system

## Common Permission Patterns

### Pattern 1: Staff Only
```php
public function __construct() {
    parent::__construct();
    $this->access_only_team_members();
}
```

### Pattern 2: Admin Only
```php
public function __construct() {
    parent::__construct();
    $this->access_only_admin();
}
```

### Pattern 3: Staff or Client
```php
public function __construct() {
    parent::__construct();
    // Check if user is logged in and is either staff or client
    if (!$this->login_user->id) {
        redirect('signin');
    }
    
    // Only allow staff and client user types
    if (!in_array($this->login_user->user_type, array("staff", "client"))) {
        show_404();
    }
}
```

### Pattern 4: Custom Check
```php
public function __construct() {
    parent::__construct();
    if (!$this->_has_custom_permission()) {
        show_404();
    }
}

private function _has_custom_permission() {
    // Your custom logic
    return true;
}
```

## Still Having Issues?

### Check Error Logs
```bash
# RISE error log
tail -f app/logs/log-*.php

# Apache error log
tail -f /var/log/apache2/error.log

# PHP error log
tail -f /var/log/php-fpm/error.log
```

### Enable Debugging
In `app/Config/Boot/production.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

### Common Error Messages

**Error**: "Access denied"
**Fix**: Check user type and permission method

**Error**: "Class not found"
**Fix**: Check namespace and file location

**Error**: "Method not found"
**Fix**: Check method exists in parent controller

**Error**: "Table doesn't exist"
**Fix**: Re-run installation to create tables

## Get Updated Plugin

The updated plugin with permission fixes is available in the package. Simply:

1. Deactivate current plugin
2. Delete old files
3. Upload updated version
4. Install and activate

## Summary

**Root Cause**: Wrong permission method used in constructor

**Solution**: Use `access_only_team_members_or_client_contact()`

**Result**: Both staff and clients can access wallet

**Time to Fix**: 2 minutes

---

## Need More Help?

- Check main README.md for full documentation
- Review INSTALLATION.md for setup steps
- Email: support@yourwebsite.com

**The updated plugin with this fix is already in your package!**
