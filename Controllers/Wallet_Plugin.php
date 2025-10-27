<?php

namespace Wallet_Plugin\Controllers;

use App\Controllers\Security_Controller;

class Wallet_Plugin extends Security_Controller {

    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!$this->login_user->id) {
            redirect('signin');
        }
        
        // Allow staff (for management) and clients (for viewing their wallet)
        if (!in_array($this->login_user->user_type, array("staff", "client"))) {
            show_404();
        }
    }
    
    // Helper to check if user is admin/staff (wallet manager)
    private function _is_wallet_manager() {
        return $this->login_user->is_admin || $this->login_user->user_type === "staff";
    }

    // Helper to check if user is a client (wallet owner)
    private function _is_client() {
        return $this->login_user->user_type === "client";
    }

    protected function _get_wallet_settings($setting_name = "") {
        $Wallet_settings_model = new \Wallet_Plugin\Models\Wallet_settings_model();
        
        if ($setting_name) {
            return $Wallet_settings_model->get_setting($setting_name);
        } else {
            return $Wallet_settings_model->get_all_settings();
        }
    }

    // Main wallet dashboard - FOR CLIENTS ONLY
    public function index() {
        // Only clients can view their own wallet
        if (!$this->_is_client()) {
            // Staff/admins should go to management page
            redirect('wallet_plugin/admin_manage_wallets');
        }
        
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $login_user_id = $this->login_user->id;
        
        // Get or create wallet for client
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));
        
        if (!$wallet || !$wallet->id) {
            // Auto-create wallet if enabled
            if ($this->_get_wallet_settings("auto_create_wallet") == "1") {
                $current_time = date('Y-m-d H:i:s');
                
                $wallet_data = array(
                    "user_id" => $login_user_id,
                    "balance" => 0,
                    "currency" => $this->_get_wallet_settings("wallet_currency") ?: "USD",
                    "created_at" => $current_time,
                    "updated_at" => $current_time
                );
                $wallet_id = $Wallet_model->ci_save($wallet_data);
                $wallet = $Wallet_model->get_one($wallet_id);
            }
        }
        
        $view_data['wallet'] = $wallet;
        
        return $this->template->rander('Wallet_Plugin\Views\index', $view_data);
    }

    // Show transactions list
    public function transactions() {
        if (!$this->_is_client()) {
            show_404();
        }
        
        return $this->template->rander('Wallet_Plugin\Views\transactions');
    }

   // Get transaction list data for datatable
public function transaction_list_data() {
    $Wallet_transactions_model = new \Wallet_Plugin\Models\Wallet_transactions_model();
    
    // Determine which user's transactions to show
    if ($this->_is_client()) {
        // Clients see only their own transactions
        $user_id = $this->login_user->id;
    } else {
        // Staff can see filtered transactions (from GET parameter)
        $user_id = $this->request->getGet('user_id');
    }
    
    $options = array();
    if ($user_id) {
        $options["user_id"] = $user_id;
    }
    
    // Get transaction data
    $list_data = $Wallet_transactions_model->get_details($options)->getResult();
    $result = array();
    
    foreach ($list_data as $data) {
        $result[] = $this->_make_transaction_row($data);
    }
    
    echo json_encode(array("data" => $result));
}

private function _make_transaction_row($data) {
    // Format transaction type with badge
    if ($data->transaction_type == "credit") {
        $transaction_type = "<span class='badge bg-success'>" . wallet_lang("credit") . "</span>";
    } else {
        $transaction_type = "<span class='badge bg-danger'>" . wallet_lang("debit") . "</span>";
    }
    
    // Format amount with color
    $amount = to_currency($data->amount, $data->currency);
    if ($data->transaction_type == "debit") {
        $amount = "<span class='text-danger'>-" . $amount . "</span>";
    } else {
        $amount = "<span class='text-success'>+" . $amount . "</span>";
    }
    
    // Get description or show dash
    $description = $data->description ?: "-";
    
    // Format balance after
    $balance_after = to_currency($data->balance_after, $data->currency);
    
    // Format date
    $date = format_to_datetime($data->created_at);
    
    return array(
        $date,
        $transaction_type,
        $amount,
        $description,
        $balance_after
    );
}

    // Modal to load funds - STAFF/ADMIN ONLY
public function load_funds_modal() {
    // Only staff/admins can load funds for clients
    if (!$this->_is_wallet_manager()) {
        echo "Access denied. Only staff can load funds for clients.";
        return;
    }
    
    // Get target_user_id from GET parameter
    $target_user_id = $this->request->getGet('target_user_id');
    
    // If no target_user_id, get client_id and convert it
    if (!$target_user_id) {
        $client_id = $this->request->getGet('client_id');
        
        if ($client_id) {
            // Get the primary contact user_id for this client
            $db = \Config\Database::connect();
            $db_prefix = $db->getPrefix();
            
            $client_contact = $db->table($db_prefix . 'users')
                ->where('client_id', $client_id)
                ->where('deleted', 0)
                ->where('is_primary_contact', 1)
                ->get()
                ->getRow();
            
            if (!$client_contact) {
                // If no primary contact, get any contact for this client
                $client_contact = $db->table($db_prefix . 'users')
                    ->where('client_id', $client_id)
                    ->where('deleted', 0)
                    ->where('user_type', 'client')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getRow();
            }
            
            if ($client_contact) {
                $target_user_id = $client_contact->id;
            }
        }
    }
    
    $view_data['target_user_id'] = $target_user_id;
    $view_data['login_user_id'] = $this->login_user->id;
    
    return $this->template->view('Wallet_Plugin\Views\load_funds_modal', $view_data);
}

// Add funds to wallet - STAFF/ADMIN LOADING FUNDS FOR CLIENTS ONLY
public function add_funds() {
    // Only staff/admins can load funds
    if (!$this->_is_wallet_manager()) {
        echo json_encode(array("success" => false, "message" => "Access denied. Only staff can load funds."));
        return;
    }
    
    $this->validate_submitted_data(array(
        "amount" => "required|numeric",
        "target_user_id" => "required|numeric"
    ));

    $amount = $this->request->getPost("amount");
    $description = $this->request->getPost("description");
    $target_user_id = $this->request->getPost("target_user_id"); // This is the USER ID (client contact)
    $login_user_id = $this->login_user->id; // STAFF/ADMIN ID

    if ($amount <= 0) {
        echo json_encode(array("success" => false, "message" => "Invalid amount"));
        return;
    }

    // Verify target user exists and is a client
    $db = \Config\Database::connect();
    $db_prefix = $db->getPrefix();
    
    $user = $db->table($db_prefix . 'users')
        ->where('id', $target_user_id)
        ->where('deleted', 0)
        ->where('user_type', 'client')
        ->get()
        ->getRow();
    
    if (!$user) {
        // Debug: Log what we're looking for
        error_log("Wallet Plugin: Looking for user_id=" . $target_user_id);
        
        // Check if user exists at all
        $any_user = $db->table($db_prefix . 'users')
            ->where('id', $target_user_id)
            ->where('deleted', 0)
            ->get()
            ->getRow();
        
        if ($any_user) {
            error_log("Wallet Plugin: User found but user_type=" . $any_user->user_type . " (expected: client)");
            echo json_encode(array(
                "success" => false, 
                "message" => "User found but not a client (user type: " . $any_user->user_type . ")"
            ));
        } else {
            error_log("Wallet Plugin: User ID " . $target_user_id . " not found in database");
            echo json_encode(array(
                "success" => false, 
                "message" => "User not found (ID: " . $target_user_id . ")"
            ));
        }
        return;
    }

    $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
    
    // Get wallet for USER (not client_id)
    $wallet = $Wallet_model->get_one_where(array(
        "user_id" => $target_user_id,
        "deleted" => 0
    ));

    if (!$wallet || !$wallet->id) {
        // Auto-create wallet for user
        $wallet_currency = $this->_get_wallet_settings("wallet_currency") ?: "USD";
        $current_time = date('Y-m-d H:i:s');
        
        $wallet_data = array(
            "user_id" => $target_user_id,
            "balance" => 0,  // Start with 0 balance
            "currency" => $wallet_currency,
            "created_at" => $current_time,
            "updated_at" => $current_time
        );
        $wallet_id = $Wallet_model->ci_save($wallet_data);
        $wallet = $Wallet_model->get_one($wallet_id);
    }

    // Get current datetime
    $current_time = date('Y-m-d H:i:s');
    $new_balance = floatval($wallet->balance) + floatval($amount);

    // Add transaction record
    $Wallet_transactions_model = new \Wallet_Plugin\Models\Wallet_transactions_model();
    
    // Get staff name for description
    $staff_name = $this->login_user->first_name . ' ' . $this->login_user->last_name;
    $default_description = "Funds loaded by " . $staff_name;
    
    $transaction_data = array(
        "wallet_id" => $wallet->id,
        "user_id" => $target_user_id,  // CLIENT USER who owns the wallet
        "transaction_type" => "credit",
        "amount" => $amount,
        "currency" => $wallet->currency,
        "reference_type" => "manual_load",
        "description" => $description ? $description : $default_description,
        "balance_before" => $wallet->balance,
        "balance_after" => $new_balance,
        "created_by" => $login_user_id,  // STAFF/ADMIN who loaded the funds
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

        // Get user's full name for success message
        $user_name = $user->first_name . ' ' . $user->last_name;

        echo json_encode(array(
            "success" => true,
            "message" => "Funds loaded successfully for " . $user_name,
            "data" => array(
                "new_balance" => number_format($new_balance, 2) . " " . $wallet->currency,
                "user_name" => $user_name
            )
        ));
    } else {
        echo json_encode(array("success" => false, "message" => "Error occurred while adding funds"));
    }
}

    // Process wallet payment for invoice - CLIENTS ONLY
    public function process_payment() {
        // Only clients can pay from their wallet
        if (!$this->_is_client()) {
            echo json_encode(array("success" => false, "message" => "Only clients can pay from wallet"));
            return;
        }
        
        $this->validate_submitted_data(array(
            "invoice_id" => "required|numeric",
            "amount" => "required|numeric"
        ));

        $invoice_id = $this->request->getPost("invoice_id");
        $amount = $this->request->getPost("amount");
        $login_user_id = $this->login_user->id;
        $current_time = date('Y-m-d H:i:s');

        // Get wallet
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));

        if (!$wallet || !$wallet->id) {
            echo json_encode(array("success" => false, "message" => "Wallet not found"));
            return;
        }

        // Check balance
        if ($wallet->balance < $amount) {
            echo json_encode(array("success" => false, "message" => "Insufficient wallet balance. Please contact staff to load funds."));
            return;
        }

        $new_balance = $wallet->balance - $amount;

        // Create transaction
        $Wallet_transactions_model = new \Wallet_Plugin\Models\Wallet_transactions_model();
        $transaction_data = array(
            "wallet_id" => $wallet->id,
            "user_id" => $login_user_id,
            "transaction_type" => "debit",
            "amount" => $amount,
            "currency" => $wallet->currency,
            "reference_type" => "invoice",
            "reference_id" => $invoice_id,
            "description" => "Payment for Invoice #" . $invoice_id,
            "balance_before" => $wallet->balance,
            "balance_after" => $new_balance,
            "created_by" => $login_user_id,
            "created_at" => $current_time
        );

        $transaction_id = $Wallet_transactions_model->insert($transaction_data);

        if ($transaction_id) {
            // Update wallet balance
            $Wallet_model->update($wallet->id, array(
                "balance" => $new_balance,
                "updated_at" => $current_time
            ));

            // Create invoice payment record
            $Invoice_payments_model = model("App\Models\Invoice_payments_model");
            $payment_data = array(
                "invoice_id" => $invoice_id,
                "payment_date" => $current_time,
                "payment_method_id" => $this->_get_wallet_payment_method_id(),
                "amount" => $amount,
                "note" => "Paid via Wallet",
                "created_by" => $login_user_id,
                "created_at" => $current_time
            );
            $Invoice_payments_model->ci_save($payment_data);

            echo json_encode(array(
                "success" => true,
                "message" => "Payment successful",
                "data" => array(
                    "new_balance" => to_currency($new_balance, $wallet->currency),
                    "transaction_id" => $transaction_id
                )
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "Error occurred while processing payment"));
        }
    }

    // Check wallet balance - CLIENTS ONLY
    public function check_balance() {
        // Only clients can check their own balance
        if (!$this->_is_client()) {
            echo json_encode(array("success" => false, "message" => "Only clients have wallets"));
            return;
        }
        
        $login_user_id = $this->login_user->id;
        
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));

        if ($wallet && $wallet->id) {
            echo json_encode(array(
                "success" => true,
                "balance" => $wallet->balance,
                "formatted_balance" => to_currency($wallet->balance, $wallet->currency),
                "currency" => $wallet->currency
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Wallet not found. Please contact support."
            ));
        }
    }

    // Settings page - ADMIN ONLY
    public function settings() {
        $this->access_only_admin_or_settings_admin();
        
        $view_data['wallet_settings'] = $this->_get_wallet_settings();
        
        return $this->template->rander('Wallet_Plugin\Views\settings', $view_data);
    }

    // Save settings - ADMIN ONLY
    public function save_settings() {
        $this->access_only_admin_or_settings_admin();

        $Wallet_settings_model = new \Wallet_Plugin\Models\Wallet_settings_model();
        
        $settings = array(
            "wallet_enabled",
            "minimum_balance",
            "allow_negative_balance",
            "auto_create_wallet",
            "wallet_currency"
        );

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            $Wallet_settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, "message" => app_lang("settings_updated")));
    }

    // Client wallet tab - VIEW ONLY
public function client_wallet($client_id) {
    if (!$client_id) {
        show_404();
    }

    // Get the primary contact user_id for this client
    $db = \Config\Database::connect();
    $db_prefix = $db->getPrefix();
    
    // Get client's primary contact (the user who logs in)
    $client_contact = $db->table($db_prefix . 'users')
        ->where('client_id', $client_id)
        ->where('deleted', 0)
        ->where('is_primary_contact', 1)
        ->get()
        ->getRow();
    
    if (!$client_contact) {
        // If no primary contact, get any contact for this client
        $client_contact = $db->table($db_prefix . 'users')
            ->where('client_id', $client_id)
            ->where('deleted', 0)
            ->where('user_type', 'client')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRow();
    }
    
    $wallet = null;
    if ($client_contact) {
        // Get wallet using the contact's user_id
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $client_contact->id,
            "deleted" => 0
        ));
    }

    $view_data['wallet'] = $wallet;
    $view_data['client_id'] = $client_id;
    $view_data['client_contact_id'] = $client_contact ? $client_contact->id : 0;
    $view_data['is_staff'] = $this->_is_wallet_manager();

    return $this->template->view('Wallet_Plugin\Views\client_wallet_tab', $view_data);
}

    // Admin manage all wallets - STAFF/ADMIN ONLY
    public function admin_manage_wallets() {
        if (!$this->_is_wallet_manager()) {
            show_404();
        }
        
        return $this->template->rander('Wallet_Plugin\Views\admin_manage_wallets');
    }

    // Admin wallet list data - STAFF/ADMIN ONLY
    public function admin_wallet_list_data() {
        if (!$this->_is_wallet_manager()) {
            echo json_encode(array("data" => array()));
            return;
        }
        
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $list_data = $Wallet_model->get_all_wallets()->getResult();
        
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_admin_wallet_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    private function _make_admin_wallet_row($data) {
        $client_link = anchor(get_uri("clients/view/" . $data->user_id), $data->user_name);
        
        $balance = to_currency($data->balance, $data->currency);
        if ($data->balance < 0) {
            $balance = "<span class='text-danger'>" . $balance . "</span>";
        }
        
        $actions = js_anchor("<i data-feather='plus-circle' class='icon-16'></i>", array(
            'title' => wallet_lang('load_funds'),
            "class" => "load-funds-action",
            "data-action-url" => get_uri("wallet_plugin/load_funds_modal"),
            "data-client-id" => $data->user_id,
            "data-title" => wallet_lang('load_funds') . " - " . $data->user_name
        ));
        
        return array(
            $client_link,
            $balance,
            format_to_datetime($data->updated_at),
            $actions
        );
    }

    // Helper methods
    private function _get_wallet_payment_method_id() {
        $Payment_methods_model = model("App\Models\Payment_methods_model");
        $method = $Payment_methods_model->get_one_where(array("type" => "wallet_payment"));
        return $method->id ?: 0;
    }

    // Check if client has sufficient balance for invoice payment - FOR ADMIN USE
public function check_client_balance() {
    if (!$this->_is_wallet_manager()) {
        echo json_encode(array("success" => false, "message" => "Access denied"));
        return;
    }
    
    $invoice_id = $this->request->getGet('invoice_id') ?: $this->request->getPost('invoice_id');
    $amount = $this->request->getGet('amount') ?: $this->request->getPost('amount');
    
    if (!$invoice_id || !$amount) {
        echo json_encode(array("success" => false, "message" => "Missing parameters"));
        return;
    }
    
    // Get invoice to find client
    $Invoice_model = model("App\Models\Invoices_model");
    $invoice = $Invoice_model->get_one($invoice_id);
    
    if (!$invoice || !$invoice->id) {
        echo json_encode(array("success" => false, "message" => "Invoice not found"));
        return;
    }
    
    // Get client's user
    $db = \Config\Database::connect();
    $db_prefix = $db->getPrefix();
    
    $client_user = $db->table($db_prefix . 'users')
        ->where('client_id', $invoice->client_id)
        ->where('deleted', 0)
        ->where('is_primary_contact', 1)
        ->get()
        ->getRow();
    
    if (!$client_user) {
        $client_user = $db->table($db_prefix . 'users')
            ->where('client_id', $invoice->client_id)
            ->where('deleted', 0)
            ->where('user_type', 'client')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRow();
    }
    
    if (!$client_user) {
        echo json_encode(array(
            "success" => false, 
            "message" => "No user account found for this client"
        ));
        return;
    }
    
    // Get wallet balance
    $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
    $wallet = $Wallet_model->get_one_where(array(
        "user_id" => $client_user->id,
        "deleted" => 0
    ));
    
    if (!$wallet || !$wallet->id) {
        echo json_encode(array(
            "success" => false,
            "message" => "Client does not have a wallet",
            "balance" => 0,
            "formatted_balance" => to_currency(0, "USD")
        ));
        return;
    }
    
    $has_sufficient_balance = ($wallet->balance >= $amount);
    
    echo json_encode(array(
        "success" => true,
        "has_sufficient_balance" => $has_sufficient_balance,
        "balance" => $wallet->balance,
        "formatted_balance" => to_currency($wallet->balance, $wallet->currency),
        "amount_required" => $amount,
        "formatted_amount_required" => to_currency($amount, $wallet->currency),
        "currency" => $wallet->currency
    ));
}
}