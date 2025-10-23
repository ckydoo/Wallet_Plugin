# Wallet Plugin - Quick Start Guide

## What is the Wallet Plugin?

The Wallet Plugin is a digital payment system for RISE CRM that allows users to:
- Store funds in a personal wallet
- Pay invoices using wallet balance
- Track all wallet transactions
- Manage funds efficiently

## 5-Minute Setup

### 1. Install (2 minutes)
```
1. Upload Wallet_Plugin folder to /plugins directory
   OR
   Upload Wallet_Plugin.zip via Settings > Plugins

2. Go to Settings > Plugins
3. Click "Install" on "Wallet Payment System"
4. Click "Activate"
```

### 2. Configure (2 minutes)
```
1. Go to Settings > Setup > Wallet Settings
2. Check "Enable Wallet" ✓
3. Check "Auto Create Wallet" ✓
4. Set Currency: USD (or your currency)
5. Click Save
```

### 3. Test (1 minute)
```
1. Click "Wallet" in left menu
2. Click "Load Funds"
3. Enter amount: 100
4. Submit
5. Balance should show $100.00
```

## How to Use

### For End Users

**View Balance**
- Click "Wallet" in menu
- See current balance on dashboard widget

**Add Funds**
1. Wallet page → "Load Funds"
2. Enter amount
3. Add description (optional)
4. Submit

**Pay Invoice**
1. Open invoice
2. See "Pay from Wallet" option
3. Check your balance
4. Click "Pay from Wallet"
5. Confirm payment

### For Admins

**View All Wallets**
- Settings > Plugins > Wallet Plugin → "Settings"
- See all user wallets and balances

**Adjust Balance**
1. Find user's wallet
2. Click adjust icon
3. Add/subtract amount
4. Add reason
5. Submit

**Configure Settings**
- Settings > Setup > Wallet Settings
- Adjust:
  - Enable/disable system
  - Currency
  - Minimum balance
  - Auto-creation

## Key Features

### ✓ Automatic Wallet Creation
- Wallets created automatically for new users
- No manual setup required

### ✓ Real-Time Balance
- Always see current balance
- Updates immediately after transactions

### ✓ Transaction History
- Complete audit trail
- Filter by date, type, amount
- Export to Excel/PDF

### ✓ Invoice Integration
- Pay invoices directly from wallet
- Balance check before payment
- Automatic transaction recording

### ✓ Dashboard Widget
- See balance at a glance
- Quick access to wallet page

### ✓ Multi-Currency Support
- Set any currency (USD, EUR, GBP, etc.)
- Consistent across transactions

### ✓ Notifications
- Email alerts for transactions
- Web notifications
- Configurable per user

## Common Tasks

### Check Transaction History
```
Wallet page → Recent Transactions table
- View date, type, amount, balance
- Sort by any column
- Search transactions
```

### Load Funds for User (Admin)
```
1. Go to user's profile
2. Click "Wallet" tab
3. Click "Add Funds"
4. Enter amount and reason
5. Submit
```

### Refund Payment (Admin)
```
1. Find original transaction
2. Create new credit transaction
3. Enter refund amount
4. Add reference to original invoice
5. Submit
```

### Export Transactions
```
1. Go to transaction history
2. Use table export options
3. Choose format (Excel, CSV, PDF)
4. Download
```

## Tips & Best Practices

### Security
- ✓ Only give wallet adjustment rights to trusted admins
- ✓ Review transactions regularly
- ✓ Keep audit trail for compliance
- ✓ Set appropriate minimum balance

### Performance
- ✓ Archive old transactions annually
- ✓ Monitor wallet table size
- ✓ Index frequently queried columns

### User Experience
- ✓ Set clear payment terms
- ✓ Enable email notifications
- ✓ Provide clear fund loading instructions
- ✓ Show balance prominently

## Troubleshooting

### Balance Not Showing
- Refresh page (Ctrl+F5)
- Check wallet was created
- Verify plugin is activated

### Can't Pay Invoice
- Check sufficient balance
- Verify payment method enabled
- Ensure invoice is not already paid

### Transaction Not Recording
- Check transaction history
- Review error logs
- Verify database connection

### Menu Not Appearing
- Clear browser cache
- Check user permissions
- Re-activate plugin

## Support Resources

- **Full Documentation**: README.md
- **Installation Guide**: INSTALLATION.md
- **Error Logs**: /app/logs/
- **Email**: support@yourwebsite.com

## Next Steps

After basic setup:
1. ✓ Configure notification preferences
2. ✓ Set up email templates
3. ✓ Train users on wallet system
4. ✓ Configure payment method text
5. ✓ Set minimum balance rules
6. ✓ Create wallet usage policy

## Quick Reference

### File Locations
```
Plugin: /plugins/Wallet_Plugin/
Logs: /app/logs/
Settings: Settings > Setup > Wallet Settings
```

### Database Tables
```
rise_wallet - User wallets
rise_wallet_transactions - All transactions
rise_wallet_settings - Configuration
```

### Important URLs
```
Wallet Page: /wallet_plugin/index
Settings: /wallet_plugin/settings
Transactions: /wallet_plugin/transactions
```

### Key Permissions
```
View Wallet: All users
Load Funds: All users
Adjust Balance: Admin only
View Settings: Admin only
Manage Wallets: Admin only
```

---

**Ready to go?** Start with the 5-minute setup above! 🚀
