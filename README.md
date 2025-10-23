# Wallet Plugin for RISE - Ultimate Project Manager & CRM

## Overview
A comprehensive wallet system plugin for RISE CRM that allows users and administrators to load funds into digital wallets and use them to pay for invoices and other transactions within the system. Clients can view their wallet balance and transaction history.

## Features

### Core Features
- **Digital Wallet System**: Each user gets a personal wallet to store funds
- **Load Funds**: Users and admins can add funds to wallets
- **Invoice Payments**: Use wallet balance to pay for invoices
- **Transaction History**: Complete audit trail of all wallet transactions
- **Balance Display**: Real-time wallet balance visible on dashboard
- **Multi-Currency Support**: Configurable currency for wallets

### User Features
- View wallet balance on dashboard widget
- Load funds into wallet
- View transaction history
- Pay invoices using wallet balance
- Real-time balance updates

### Admin Features
- Manage all user wallets
- Adjust wallet balances
- View all transactions across the system
- Configure wallet settings
- Enable/disable wallet system
- Set minimum balance requirements

### Client Portal Features
- Clients can view their wallet balance
- Access to transaction history
- Pay invoices directly from wallet
- Load funds request

## Installation

1. **Download the Plugin**
   - Download the Wallet_Plugin folder

2. **Upload to RISE**
   - Option A: Upload the entire `Wallet_Plugin` folder to your RISE installation's plugin directory
   - Option B: Create a ZIP file of the `Wallet_Plugin` folder and upload via Settings > Plugins

3. **Install the Plugin**
   - Go to Settings > Plugins in your RISE dashboard
   - Find "Wallet Payment System" in the list
   - Click "Install"
   - Enter purchase code if required (or use any number like 111111-222222 for testing)
   - Click "Activate"

4. **Configure Settings**
   - After activation, go to Settings > Setup > Wallet Settings
   - Configure your preferences:
     - Enable/disable wallet system
     - Set default currency
     - Configure minimum balance
     - Enable auto-creation of wallets

## Database Structure

The plugin creates three main tables:

### 1. wallet
Stores wallet information for each user
- `id`: Primary key
- `user_id`: Reference to user
- `balance`: Current wallet balance
- `currency`: Wallet currency
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### 2. wallet_transactions
Records all wallet transactions
- `id`: Primary key
- `wallet_id`: Reference to wallet
- `user_id`: User who owns the wallet
- `transaction_type`: 'credit' or 'debit'
- `amount`: Transaction amount
- `currency`: Transaction currency
- `reference_type`: Type of transaction (invoice, manual, etc.)
- `reference_id`: Reference to related entity
- `description`: Transaction description
- `balance_before`: Balance before transaction
- `balance_after`: Balance after transaction
- `created_by`: User who created the transaction
- `created_at`: Transaction timestamp

### 3. wallet_settings
Stores plugin configuration
- `id`: Primary key
- `setting_name`: Setting identifier
- `setting_value`: Setting value

## Usage

### For Users

#### Viewing Wallet Balance
1. Navigate to "Wallet" from the left menu
2. Your current balance is displayed prominently
3. Recent transactions are listed below

#### Loading Funds
1. Go to Wallet page
2. Click "Load Funds" button
3. Enter amount and optional description
4. Submit the request
5. Funds are added to your wallet

#### Paying Invoices
1. Open an invoice
2. If wallet payment is enabled, you'll see the "Pay from Wallet" option
3. Your current balance is displayed
4. If sufficient balance exists, click "Pay from Wallet"
5. Confirm the payment
6. Transaction is recorded and invoice is paid

### For Administrators

#### Managing Wallets
1. Go to Settings > Plugins
2. Click "Settings" under Wallet Plugin
3. Configure global wallet settings

#### Viewing All Wallets
1. Access the wallet management section
2. View all user wallets and their balances
3. Adjust balances as needed

#### Adjusting Balances
1. Find the user's wallet
2. Click "Adjust Balance"
3. Add or deduct amount with reason
4. Transaction is recorded

## Configuration Options

### Wallet Settings
- **Enable Wallet**: Turn the wallet system on/off
- **Auto Create Wallet**: Automatically create wallets for new users
- **Wallet Currency**: Default currency (USD, EUR, GBP, etc.)
- **Minimum Balance**: Required minimum balance
- **Allow Negative Balance**: Whether users can have negative balances

### Payment Method Settings
- **Pay Button Text**: Customize the payment button label
- **Wallet Description**: Description shown on invoice payment page

## File Structure

```
Wallet_Plugin/
├── index.php (Main plugin file with hooks and metadata)
├── Config/
│   └── Routes.php (URL routing configuration)
├── Controllers/
│   └── Wallet_Plugin.php (Main controller)
├── Models/
│   ├── Wallet_model.php
│   ├── Wallet_transactions_model.php
│   └── Wallet_settings_model.php
├── Views/
│   ├── index.php (Main wallet dashboard)
│   ├── load_funds_modal.php
│   ├── invoice_payment.php
│   ├── settings.php
│   ├── client_wallet_tab.php
│   ├── user_wallet_tab.php
│   └── widgets/
│       └── wallet_balance_widget.php
├── Language/
│   └── english/
│       └── wallet_lang.php
└── index.html (Security file)
```

## Hooks Used

The plugin integrates with RISE using these hooks:
- `app_filter_payment_method_settings` - Adds wallet payment method
- `app_hook_invoice_payment_extension` - Shows wallet payment on invoices
- `app_filter_staff_left_menu` - Adds wallet menu for staff
- `app_filter_client_left_menu` - Adds wallet menu for clients
- `app_filter_dashboard_widget` - Adds wallet balance widget
- `app_filter_notification_config` - Configures wallet notifications
- `app_filter_admin_settings_menu` - Adds settings menu item
- `app_filter_client_details_ajax_tab` - Adds wallet tab to client details
- `app_filter_staff_profile_ajax_tab` - Adds wallet tab to staff profile

## Notifications

The plugin sends notifications for:
- **Wallet Credited**: When funds are added to wallet
- **Wallet Debited**: When funds are deducted from wallet
- **Wallet Low Balance**: When balance falls below threshold (optional)

## Security Features

- CSRF protection on all forms
- Input validation and sanitization
- Database queries use prepared statements
- Access control checks on all operations
- Secure transaction recording

## Best Practices

1. **Regular Backups**: Backup wallet and transaction tables regularly
2. **Audit Trail**: Keep all transaction records for compliance
3. **Balance Verification**: Periodically verify wallet balances match transactions
4. **Currency Consistency**: Use same currency across related entities
5. **Access Control**: Limit wallet adjustment permissions to trusted admins

## Troubleshooting

### Plugin Won't Install
- Ensure you're running RISE 2.8 or higher
- Check file permissions on the plugins directory
- Verify database connection

### Wallet Not Showing
- Ensure plugin is activated
- Check user permissions
- Verify "Enable Wallet" setting is on

### Payment Not Processing
- Verify sufficient wallet balance
- Check wallet payment method is enabled
- Review transaction logs

### Balance Mismatch
- Run balance reconciliation
- Check transaction history for errors
- Contact support if issue persists

## Customization

### Adding Custom Transaction Types
Edit `Wallet_transactions_model.php` to add new transaction types beyond 'credit' and 'debit'.

### Custom Notifications
Add new notification events in the `index.php` installation hook and notification config.

### Styling
Customize the wallet interface by editing view files in the `Views/` directory.

## Support

For support, issues, or feature requests:
- Email: your-support-email@example.com
- Documentation: https://yourwebsite.com/docs
- GitHub: https://github.com/yourusername/wallet-plugin

## Version History

### Version 1.0 (Current)
- Initial release
- Core wallet functionality
- Invoice payment integration
- Admin management features
- Dashboard widget
- Multi-currency support
- Transaction history
- Notification system

## Requirements

- RISE CRM version 2.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- CodeIgniter 4

## License

This plugin is proprietary software. Unauthorized distribution or modification is prohibited.

## Credits

Developed for RISE - Ultimate Project Manager & CRM by FairSketch
Plugin Author: Your Name
Website: https://yourwebsite.com

## Changelog

**1.0.0** - Initial Release
- Wallet system implementation
- Payment method integration
- Admin management interface
- Dashboard widgets
- Notification system
- Multi-language support (English)
