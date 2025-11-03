<?php
//Prevent direct access
defined('PLUGINPATH') or exit('No direct script access allowed');

/*
Plugin Name: Wallet Payment System
Plugin URL: https://yourwebsite.com/wallet-plugin
Description: A comprehensive wallet system where staff/admins load funds into client wallets. Clients use these funds to pay for invoices. Complete transaction tracking and management system.
Version: 1.0
Requires at least: 2.8
Author: Ckydoo Chikangaiso
Author URL: codzlabzim53@gmail.com
*/

// Define wallet_lang helper function for plugin
if (!function_exists('wallet_lang')) {
    function wallet_lang($key) {
        static $lang_data = null;
        
        // Load language file on first call
        if ($lang_data === null) {
            $lang_data = array();
            $user_language = get_setting('user_language') ?: 'english';
            $lang_file = PLUGINPATH . 'Wallet_Plugin/Language/' . $user_language . '/wallet_plugin_lang.php';
            
            // Fallback to English if user language file doesn't exist
            if (!file_exists($lang_file)) {
                $lang_file = PLUGINPATH . 'Wallet_Plugin/Language/english/wallet_plugin_lang.php';
            }
            
            if (file_exists($lang_file)) {
                $lang = array();
                include($lang_file);
                $lang_data = $lang;
            }
        }
        
        // Return language value or key if not found
        return isset($lang_data[$key]) ? $lang_data[$key] : ucwords(str_replace('_', ' ', $key));
    }
}

// IMPORTANT: Load plugin language into RISE's core language system EARLY
app_hooks()->add_action('app_hook_pre_controller', function() {
    $user_language = get_setting('user_language') ?: 'english';
    $lang_file = PLUGINPATH . 'Wallet_Plugin/Language/' . $user_language . '/wallet_plugin_lang.php';
    
    // Fallback to English
    if (!file_exists($lang_file)) {
        $lang_file = PLUGINPATH . 'Wallet_Plugin/Language/english/wallet_plugin_lang.php';
    }
    
    if (file_exists($lang_file)) {
        $lang = array();
        include($lang_file);
        
        // Inject into RISE's language system
        if (!isset($GLOBALS['_app_lang'])) {
            $GLOBALS['_app_lang'] = array();
        }
        
        foreach ($lang as $key => $value) {
            $GLOBALS['_app_lang'][$key] = $value;
        }
    }
});

// Register installation hook
register_installation_hook("Wallet_Plugin", function ($item_purchase_code) {
    $db = db_connect('default');
    $db_prefix = $db->getPrefix();
    $db->query("SET sql_mode = ''");
    
    // Create wallet table
    $db->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "wallet` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
        `currency` varchar(10) NOT NULL DEFAULT 'USD',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `deleted` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
    
    // Create wallet transactions table
    $db->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "wallet_transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `wallet_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `transaction_type` varchar(50) NOT NULL COMMENT 'credit, debit',
        `amount` decimal(20,2) NOT NULL,
        `currency` varchar(10) NOT NULL DEFAULT 'USD',
        `reference_type` varchar(50) NULL COMMENT 'invoice, manual_load, etc',
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
    
    // Create wallet settings table
    $db->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "wallet_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_name` varchar(100) NOT NULL,
        `setting_value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `deleted` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_name` (`setting_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
    
    // Insert default wallet settings
    $db->query("INSERT INTO `" . $db_prefix . "wallet_settings` (`setting_name`, `setting_value`, `deleted`) VALUES
        ('wallet_enabled', '1', 0),
        ('minimum_balance', '0.00', 0),
        ('allow_negative_balance', '0', 0),
        ('auto_create_wallet', '1', 0),
        ('wallet_currency', 'USD', 0)
    ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    
    // Add payment method for wallet
    $db->query("INSERT INTO `" . $db_prefix . "payment_methods` (`title`, `type`, `description`, `online_payable`, `available_on_invoice`, `minimum_payment_amount`, `settings`, `deleted`) VALUES
        ('Wallet Payment', 'wallet_payment', 'Pay using your wallet balance', 1, 1, 0, '', 0)
    ON DUPLICATE KEY UPDATE deleted=0");
    
    // Add notification settings for wallet
    $db->query("INSERT INTO `" . $db_prefix . "notification_settings` (`event`, `category`, `enable_email`, `enable_web`, `notify_to_team`, `notify_to_team_members`, `notify_to_terms`, `deleted`) VALUES
        ('wallet_credited', 'wallet', 1, 1, '', '', '', 0),
        ('wallet_debited', 'wallet', 1, 1, '', '', '', 0)
    ON DUPLICATE KEY UPDATE deleted=0");
    
    echo json_encode(array("success" => true, 'message' => "Wallet plugin installed successfully!"));
});

// Register uninstallation hook
register_uninstallation_hook("Wallet_Plugin", function () {
    // Optional: cleanup code
});

// Register activation hook
register_activation_hook("Wallet_Plugin", function () {
    $db = db_connect('default');
    $db_prefix = $db->getPrefix();
    
    $db->query("UPDATE `" . $db_prefix . "payment_methods` SET deleted = 0 WHERE type = 'wallet_payment'");
    $db->query("UPDATE `" . $db_prefix . "notification_settings` SET deleted = 0 WHERE category = 'wallet'");
});

// Register deactivation hook
register_deactivation_hook("Wallet_Plugin", function () {
    $db = db_connect('default');
    $db_prefix = $db->getPrefix();
    
    $db->query("UPDATE `" . $db_prefix . "payment_methods` SET deleted = 1 WHERE type = 'wallet_payment'");
    $db->query("UPDATE `" . $db_prefix . "notification_settings` SET deleted = 1 WHERE category = 'wallet'");
});

// For invoice payment methods list
app_hooks()->add_filter('app_filter_invoice_payment_methods', function($payment_methods) {
    $db = db_connect('default');
    $db_prefix = $db->getPrefix();
    
    $wallet_method = $db->table($db_prefix . 'payment_methods')
        ->where('type', 'wallet_payment')
        ->where('deleted', 0)
        ->where('available_on_invoice', 1)
        ->get()
        ->getRow();
    
    if ($wallet_method) {
        $payment_methods[] = $wallet_method;
    }
    
    return $payment_methods;
});
// Add action links in Settings > Plugins
app_hooks()->add_filter('app_filter_action_links_of_Wallet_Plugin', function ($action_links_array) {
    $action_links_array = array(
        \anchor(\get_uri("wallet_plugin/settings"), "Settings")
    );
    return $action_links_array;
});
// Add payment method settings
app_hooks()->add_filter('app_filter_payment_method_settings', function($settings) {
    $settings["wallet_payment"] = array(
        array("name" => "pay_button_text", "text" => "Pay Button Text", "type" => "text", "default" => "Pay from Wallet"),
        array("name" => "wallet_description", "text" => "Wallet Description", "type" => "text", "default" => "Use your wallet balance to make this payment"),
    );
    return $settings;
});

// Make sure wallet payment method is available

app_hooks()->add_filter('app_filter_available_payment_methods', function($payment_methods) {
    $db = db_connect('default');
    $db_prefix = $db->getPrefix();
    
    // Check if wallet payment method exists and is active
    $method = $db->table($db_prefix . 'payment_methods')
        ->where('type', 'wallet_payment')
        ->where('deleted', 0)
        ->get()
        ->getRow();
    
    if ($method) {
        // Check if wallet system is enabled
        $wallet_enabled = $db->table($db_prefix . 'wallet_settings')
            ->where('setting_name', 'wallet_enabled')
            ->where('deleted', 0)
            ->get()
            ->getRow();
        
        if ($wallet_enabled && $wallet_enabled->setting_value == '1') {
            $payment_methods[] = array(
                'id' => $method->id,
                'type' => 'wallet_payment',
                'title' => $method->title,
                'description' => $method->description
            );
        }
    }
    
    return $payment_methods;
});

// Show wallet payment option in invoice view
app_hooks()->add_action('app_hook_invoice_payment_extension', function($payment_method_variables = array()) {
    // Ensure we have valid data
    if (!is_array($payment_method_variables)) {
        return;
    }
    
    $method_type = get_array_value($payment_method_variables, "method_type");
    
    // Only show our payment view for wallet_payment method
    if ($method_type === "wallet_payment") {
        echo view("Wallet_Plugin\Views\invoice_payment", array(
            'payment_method_variables' => $payment_method_variables
        ));
    }
});

// app_hooks()->add_filter('app_filter_payment_methods', function($payment_methods) {
//     $payment_methods[] = array(
//         "id" => "wallet_payment",
//         "text" => "Wallet Payment"
//     );
//     return $payment_methods;
// });
// Client menu - shows "My Wallet"
app_hooks()->add_filter('app_filter_client_left_menu', function ($sidebar_menu) {
    $sidebar_menu["wallet"] = array(
        "name" => "my_wallet",
        "url" => "wallet_plugin/index",
        "class" => "credit-card",
        "position" => 10
    );
    return $sidebar_menu;
});

// Staff/Admin menu - shows "Manage Wallets"  
app_hooks()->add_filter('app_filter_staff_left_menu', function ($sidebar_menu) {
    $sidebar_menu["wallet_management"] = array(
        "name" => "manage_wallets",
        "url" => "wallet_plugin/admin_manage_wallets",
        "class" => "credit-card",
        "position" => 15
    );
    return $sidebar_menu;
});


// Add wallet widget to CLIENT dashboard only
app_hooks()->add_filter('app_filter_dashboard_widget', function ($default_widgets_array) {
    // Only show widget for clients
    $login_user = app('login')->getUser();
    if ($login_user && $login_user->user_type === "client") {
        array_push($default_widgets_array, array(
            "widget" => "wallet_balance",
            "widget_view" => view("Wallet_Plugin\Views\widgets\wallet_balance_widget")
        ));
    }
    return $default_widgets_array;
});

// Add notification category
app_hooks()->add_filter('app_filter_notification_category_suggestion', function ($category_suggestions) {
    $category_suggestions[] = array("id" => "wallet", "text" => "Wallet");
    return $category_suggestions;
});

// Add notification config
app_hooks()->add_filter('app_filter_notification_config', function ($events_of_hook) {
    $notification_link = function () {
        return array("url" => \get_uri("wallet_plugin/index"));
    };
    
    $events_of_hook["wallet_credited"] = array(
        "notify_to" => array("recipient"),
        "info" => $notification_link
    );
    
    $events_of_hook["wallet_debited"] = array(
        "notify_to" => array("recipient"),
        "info" => $notification_link
    );
    
    return $events_of_hook;
});

// Add settings menu item
app_hooks()->add_filter('app_filter_admin_settings_menu', function($settings_menu) {
    $settings_menu["setup"][] = array(
        "name" => "Wallet Settings",
        "url" => "wallet_plugin/settings"
    );
    return $settings_menu;
});

// Add client details tab - staff can see client's wallet
app_hooks()->add_filter('app_filter_client_details_ajax_tab', function ($hook_tabs, $client_id) {
    $hook_tabs[] = array(
        "title" => 'Wallet',
        "url" => \get_uri("wallet_plugin/client_wallet/" . $client_id),
        "target" => "wallet-tab"
    );
    return $hook_tabs;
});
// Hook to process wallet payment when admin records payment manually
app_hooks()->add_action('app_hook_payment_received', function($payment_id) {
    try {
        // Get payment details
        $Payment_model = model("App\Models\Invoice_payments_model");
        $payment = $Payment_model->get_one($payment_id);
        
        if (!$payment || !$payment->id) {
            return;
        }
        
        // Check if this is a wallet payment
        $Payment_methods_model = model("App\Models\Payment_methods_model");
        $payment_method = $Payment_methods_model->get_one($payment->payment_method_id);
        
        if (!$payment_method || $payment_method->type !== 'wallet_payment') {
            return; // Not a wallet payment, skip
        }
        
        // Get invoice to find the client
        $Invoice_model = model("App\Models\Invoices_model");
        $invoice = $Invoice_model->get_one($payment->invoice_id);
        
        if (!$invoice || !$invoice->id) {
            error_log("Wallet Plugin: Invoice not found for payment ID: $payment_id");
            return;
        }
        
        // Get client's primary contact user
        $db = db_connect('default');
        $db_prefix = $db->getPrefix();
        
        $client_user = $db->table($db_prefix . 'users')
            ->where('client_id', $invoice->client_id)
            ->where('deleted', 0)
            ->where('is_primary_contact', 1)
            ->get()
            ->getRow();
        
        if (!$client_user) {
            // Try any contact for this client
            $client_user = $db->table($db_prefix . 'users')
                ->where('client_id', $invoice->client_id)
                ->where('deleted', 0)
                ->where('user_type', 'client')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRow();
        }
        
        if (!$client_user) {
            error_log("Wallet Plugin: No user found for client_id: " . $invoice->client_id);
            return;
        }
        
        // Get or create wallet for this user
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $client_user->id,
            "deleted" => 0
        ));
        
        if (!$wallet || !$wallet->id) {
            // Auto-create wallet
            $Wallet_settings_model = new \Wallet_Plugin\Models\Wallet_settings_model();
            $wallet_currency = $Wallet_settings_model->get_setting("wallet_currency") ?: "USD";
            $current_time = date('Y-m-d H:i:s');
            
            $wallet_data = array(
                "user_id" => $client_user->id,
                "balance" => 0,
                "currency" => $wallet_currency,
                "created_at" => $current_time,
                "updated_at" => $current_time
            );
            $wallet_id = $Wallet_model->ci_save($wallet_data);
            $wallet = $Wallet_model->get_one($wallet_id);
        }
        
        // Check if wallet has sufficient balance
        if ($wallet->balance < $payment->amount) {
            error_log("Wallet Plugin: Insufficient balance. Required: {$payment->amount}, Available: {$wallet->balance}");
            
            // Optionally delete the payment record or mark it as failed
            // For now, we'll just log the error
            return;
        }
        
        // Deduct from wallet
        $current_time = date('Y-m-d H:i:s');
        $new_balance = $wallet->balance - $payment->amount;
        
        // Create wallet transaction
        $Wallet_transactions_model = new \Wallet_Plugin\Models\Wallet_transactions_model();
        
        // Get who created the payment (admin/staff)
        $created_by = $payment->created_by ?: 1; // Default to admin if not set
        
        $transaction_data = array(
            "wallet_id" => $wallet->id,
            "user_id" => $client_user->id,
            "transaction_type" => "debit",
            "amount" => $payment->amount,
            "currency" => $wallet->currency,
            "reference_type" => "invoice",
            "reference_id" => $payment->invoice_id,
            "description" => "Payment for Invoice #" . $invoice->id . " (recorded by admin)",
            "balance_before" => $wallet->balance,
            "balance_after" => $new_balance,
            "created_by" => $created_by,
            "created_at" => $current_time,
            "deleted" => 0
        );
        
        $transaction_id = $Wallet_transactions_model->ci_save($transaction_data);
        
        if ($transaction_id) {
            // Update wallet balance
            $wallet_update = array(
                "balance" => $new_balance,
                "updated_at" => $current_time
            );
            $Wallet_model->ci_save($wallet_update, $wallet->id);
            
            error_log("Wallet Plugin: Successfully processed wallet payment. Transaction ID: $transaction_id");
        }
        
    } catch (\Exception $e) {
        error_log("Wallet Plugin Error in payment_received hook: " . $e->getMessage());
    }
});
// Inject JavaScript for admin payment balance checking
app_hooks()->add_action('app_hook_before_invoice_view_render', function() {
    echo view("Wallet_Plugin\Views\admin_payment_check");
});