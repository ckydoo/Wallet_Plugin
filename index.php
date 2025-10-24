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

// Show wallet payment option in invoice view
app_hooks()->add_action('app_hook_invoice_payment_extension', function($payment_method_variables) {
    if (get_array_value($payment_method_variables, "method_type") === "wallet_payment") {
        echo view("Wallet_Plugin\Views\invoice_payment", $payment_method_variables);
    }
});

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