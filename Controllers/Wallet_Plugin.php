<?php

namespace Wallet_Plugin\Controllers;

use App\Controllers\Security_Controller;

class Wallet_Plugin extends Security_Controller {

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
    
    // Helper to check if user can access wallet
    private function _can_access_wallet() {
        return $this->login_user->user_type === "staff" || $this->login_user->user_type === "client";
    }

    protected function _get_wallet_settings($setting_name = "") {
        $Wallet_settings_model = new \Wallet_Plugin\Models\Wallet_settings_model();
        
        if ($setting_name) {
            return $Wallet_settings_model->get_setting($setting_name);
        } else {
            return $Wallet_settings_model->get_all_settings();
        }
    }

    // Main wallet dashboard
    public function index() {
        // Check if user can access wallet
        if (!$this->_can_access_wallet()) {
            show_404();
        }
        
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $login_user_id = $this->login_user->id;
        
        // Get or create wallet for user
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));
        
        if (!$wallet->id) {
            // Auto-create wallet if enabled
            if ($this->_get_wallet_settings("auto_create_wallet") == "1") {
                $wallet_data = array(
                    "user_id" => $login_user_id,
                    "balance" => 0,
                    "currency" => $this->_get_wallet_settings("wallet_currency") ?: "USD",
                    "created_at" => \get_current_utc_date_time(),
                    "updated_at" => \get_current_utc_date_time()
                );
                $wallet_id = $Wallet_model->ci_save($wallet_data);
                $wallet = $Wallet_model->get_one($wallet_id);
            }
        }
        
        $view_data['wallet'] = $wallet;
        $view_data['can_manage_wallet'] = $this->can_manage_wallet();
        
        return $this->template->rander('Wallet_Plugin\Views\index', $view_data);
    }

    // Show transactions list
    public function transactions() {
        return $this->template->rander('Wallet_Plugin\Views\transactions');
    }

    // Get transaction list data for datatable
    public function transaction_list_data() {
        $Wallet_transactions_model = new \Wallet_Plugin\Models\Wallet_transactions_model();
        $login_user_id = $this->login_user->id;
        
        $options = array(
            "user_id" => $login_user_id
        );
        
        $list_data = $Wallet_transactions_model->get_details($options)->getResult();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_transaction_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    private function _make_transaction_row($data) {
        $transaction_type = $data->transaction_type == "credit" 
            ? "<span class='badge badge-success'>" . \app_lang("credit") . "</span>"
            : "<span class='badge badge-danger'>" . \app_lang("debit") . "</span>";
        
        $amount = \to_currency($data->amount, $data->currency);
        if ($data->transaction_type == "debit") {
            $amount = "-" . $amount;
        } else {
            $amount = "+" . $amount;
        }
        
        return array(
            \format_to_datetime($data->created_at),
            $transaction_type,
            $amount,
            $data->description ?: "-",
            \to_currency($data->balance_after, $data->currency)
        );
    }

    // Modal to load funds
    public function load_funds_modal() {
        $view_data['wallet_currency'] = $this->_get_wallet_settings("wallet_currency") ?: "USD";
        return $this->template->view('Wallet_Plugin\Views\load_funds_modal', $view_data);
    }

    // Add funds to wallet
    public function add_funds() {
        $this->validate_submitted_data(array(
            "amount" => "required|numeric"
        ));

        $amount = $this->request->getPost("amount");
        $description = $this->request->getPost("description");
        $login_user_id = $this->login_user->id;

        if ($amount <= 0) {
            echo json_encode(array("success" => false, "message" => \app_lang("invalid_amount")));
            return;
        }

        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));

        if (!$wallet->id) {
            echo json_encode(array("success" => false, "message" => \app_lang("wallet_not_found")));
            return;
        }

        // Add transaction
        $Wallet_transactions_model = new \Wallet_Plugin\Models\Wallet_transactions_model();
        $transaction_data = array(
            "wallet_id" => $wallet->id,
            "user_id" => $login_user_id,
            "transaction_type" => "credit",
            "amount" => $amount,
            "currency" => $wallet->currency,
            "reference_type" => "manual",
            "description" => $description ?: "Manual funds added",
            "balance_before" => $wallet->balance,
            "balance_after" => $wallet->balance + $amount,
            "created_by" => $login_user_id,
            "created_at" => \get_current_utc_date_time()
        );

        $transaction_id = $Wallet_transactions_model->ci_save($transaction_data);

        if ($transaction_id) {
            // Update wallet balance
            $Wallet_model->ci_save(array(
                "balance" => $wallet->balance + $amount,
                "updated_at" => \get_current_utc_date_time()
            ), $wallet->id);

            // Send notification
            \log_notification("wallet_credited", array(
                "wallet_transaction_id" => $transaction_id
            ), $login_user_id);

            echo json_encode(array(
                "success" => true,
                "message" => \app_lang("funds_added_successfully"),
                "data" => array(
                    "new_balance" => \to_currency($wallet->balance + $amount, $wallet->currency)
                )
            ));
        } else {
            echo json_encode(array("success" => false, "message" => \app_lang("error_occurred")));
        }
    }

    // Process wallet payment for invoice
    public function process_payment() {
        $this->validate_submitted_data(array(
            "invoice_id" => "required|numeric",
            "amount" => "required|numeric"
        ));

        $invoice_id = $this->request->getPost("invoice_id");
        $amount = $this->request->getPost("amount");
        $login_user_id = $this->login_user->id;

        // Get wallet
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));

        if (!$wallet->id) {
            echo json_encode(array("success" => false, "message" => \app_lang("wallet_not_found")));
            return;
        }

        // Check balance
        if ($wallet->balance < $amount) {
            echo json_encode(array("success" => false, "message" => \app_lang("insufficient_balance")));
            return;
        }

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
            "balance_after" => $wallet->balance - $amount,
            "created_by" => $login_user_id,
            "created_at" => \get_current_utc_date_time()
        );

        $transaction_id = $Wallet_transactions_model->ci_save($transaction_data);

        if ($transaction_id) {
            // Update wallet balance
            $new_balance = $wallet->balance - $amount;
            $Wallet_model->ci_save(array(
                "balance" => $new_balance,
                "updated_at" => \get_current_utc_date_time()
            ), $wallet->id);

            // Create invoice payment record
            $Invoice_payments_model = model("App\Models\Invoice_payments_model");
            $payment_data = array(
                "invoice_id" => $invoice_id,
                "payment_date" => \get_current_utc_date_time(),
                "payment_method_id" => $this->_get_wallet_payment_method_id(),
                "amount" => $amount,
                "note" => "Paid via Wallet",
                "created_by" => $login_user_id,
                "created_at" => \get_current_utc_date_time()
            );
            $Invoice_payments_model->ci_save($payment_data);

            // Send notification
            \log_notification("wallet_debited", array(
                "wallet_transaction_id" => $transaction_id
            ), $login_user_id);

            echo json_encode(array(
                "success" => true,
                "message" => \app_lang("payment_successful"),
                "data" => array(
                    "new_balance" => \to_currency($new_balance, $wallet->currency),
                    "transaction_id" => $transaction_id
                )
            ));
        } else {
            echo json_encode(array("success" => false, "message" => \app_lang("error_occurred")));
        }
    }

    // Check wallet balance
    public function check_balance() {
        $login_user_id = $this->login_user->id;
        
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $login_user_id,
            "deleted" => 0
        ));

        if ($wallet->id) {
            echo json_encode(array(
                "success" => true,
                "balance" => $wallet->balance,
                "formatted_balance" => \to_currency($wallet->balance, $wallet->currency),
                "currency" => $wallet->currency
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => \app_lang("wallet_not_found")
            ));
        }
    }

    // Settings page
    public function settings() {
        $this->access_only_admin_or_settings_admin();
        
        $view_data['wallet_settings'] = $this->_get_wallet_settings();
        
        return $this->template->rander('Wallet_Plugin\Views\settings', $view_data);
    }

    // Save settings
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

        echo json_encode(array("success" => true, "message" => \app_lang("settings_updated")));
    }

    // Client wallet tab
    public function client_wallet($client_id) {
        if (!$client_id) {
            show_404();
        }

        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $client_id,
            "deleted" => 0
        ));

        $view_data['wallet'] = $wallet;
        $view_data['client_id'] = $client_id;
        $view_data['can_manage_wallet'] = $this->can_manage_wallet();

        return $this->template->view('Wallet_Plugin\Views\client_wallet_tab', $view_data);
    }

    // User wallet tab
    public function user_wallet($user_id) {
        if (!$user_id) {
            show_404();
        }

        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $wallet = $Wallet_model->get_one_where(array(
            "user_id" => $user_id,
            "deleted" => 0
        ));

        $view_data['wallet'] = $wallet;
        $view_data['user_id'] = $user_id;
        $view_data['can_manage_wallet'] = $this->can_manage_wallet();

        return $this->template->view('Wallet_Plugin\Views\user_wallet_tab', $view_data);
    }

    // Admin manage all wallets
    public function admin_manage_wallets() {
        $this->access_only_admin();
        
        return $this->template->rander('Wallet_Plugin\Views\admin_manage_wallets');
    }

    // Admin wallet list data
    public function admin_wallet_list_data() {
        $this->access_only_admin();
        
        $Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
        $list_data = $Wallet_model->get_all_wallets()->getResult();
        
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_admin_wallet_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    private function _make_admin_wallet_row($data) {
        $user_link = \anchor(\get_uri("team_members/view/" . $data->user_id), $data->user_name);
        
        $actions = modal_\anchor(\get_uri("wallet_plugin/admin_adjust_balance/" . $data->id), 
            "<i data-feather='edit' class='icon-16'></i>", 
            array("class" => "edit", "title" => \app_lang("adjust_balance"), "data-post-id" => $data->id));
        
        return array(
            $user_link,
            \to_currency($data->balance, $data->currency),
            \format_to_datetime($data->updated_at),
            $actions
        );
    }

    // Helper methods
    private function can_manage_wallet() {
        return $this->login_user->is_admin || $this->login_user->user_type === "staff";
    }

    private function _get_wallet_payment_method_id() {
        $Payment_methods_model = model("App\Models\Payment_methods_model");
        $method = $Payment_methods_model->get_one_where(array("type" => "wallet_payment"));
        return $method->id ?: 0;
    }
}
