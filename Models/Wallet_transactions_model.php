<?php

namespace Wallet_Plugin\Models;

use App\Models\Crud_model;

class Wallet_transactions_model extends Crud_model {

    protected $table = null;

    public function __construct() {
        // Pass ONLY the table name without prefix to parent
        $this->table = 'wallet_transactions';
        parent::__construct($this->table);
    }

    public function get_details($options = array()) {
        $db = \Config\Database::connect();
        $transactions_table = $db->getPrefix() . $this->table;
        $users_table = $db->getPrefix() . 'users';
        $wallets_table = $db->getPrefix() . 'wallet';

        $where = "";

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $transactions_table.user_id=$user_id";
        }

        $wallet_id = $this->_get_clean_value($options, "wallet_id");
        if ($wallet_id) {
            $where .= " AND $transactions_table.wallet_id=$wallet_id";
        }

        $transaction_type = $this->_get_clean_value($options, "transaction_type");
        if ($transaction_type) {
            $where .= " AND $transactions_table.transaction_type='$transaction_type'";
        }

        $reference_type = $this->_get_clean_value($options, "reference_type");
        if ($reference_type) {
            $where .= " AND $transactions_table.reference_type='$reference_type'";
        }

        $reference_id = $this->_get_clean_value($options, "reference_id");
        if ($reference_id) {
            $where .= " AND $transactions_table.reference_id=$reference_id";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $skip = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $skip ";
        }

        $sql = "SELECT $transactions_table.*, 
                CONCAT($users_table.first_name, ' ', $users_table.last_name) as created_by_user,
                $wallets_table.currency as wallet_currency
                FROM $transactions_table
                LEFT JOIN $users_table ON $users_table.id = $transactions_table.created_by
                LEFT JOIN $wallets_table ON $wallets_table.id = $transactions_table.wallet_id
                WHERE $transactions_table.deleted=0 $where
                ORDER BY $transactions_table.created_at DESC
                $limit_offset";

        return $this->db->query($sql);
    }

    public function get_transaction_summary($user_id) {
        $db = \Config\Database::connect();
        $transactions_table = $db->getPrefix() . $this->table;

        $sql = "SELECT 
                SUM(CASE WHEN transaction_type='credit' THEN amount ELSE 0 END) as total_credit,
                SUM(CASE WHEN transaction_type='debit' THEN amount ELSE 0 END) as total_debit,
                COUNT(id) as total_transactions
                FROM $transactions_table
                WHERE user_id = ? AND deleted = 0";

        $result = $this->db->query($sql, array($user_id))->getRow();
        return $result;
    }

    public function get_recent_transactions($user_id, $limit = 10) {
        $options = array(
            "user_id" => $user_id,
            "limit" => $limit
        );

        return $this->get_details($options)->getResult();
    }
}
