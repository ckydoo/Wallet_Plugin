<?php

namespace Wallet_Plugin\Models;

use App\Models\Crud_model;

class Wallet_model extends Crud_model {

    protected $table = null;

    public function __construct() {
        // Pass ONLY the table name without prefix to parent
        // Parent Crud_model will add the prefix automatically
        $this->table = 'wallet';
        parent::__construct($this->table);
    }

    public function get_details($options = array()) {
        $db = \Config\Database::connect();
        $wallets_table = $db->getPrefix() . $this->table;
        $users_table = $db->getPrefix() . 'users';

        $where = "";
        $user_id = $this->_get_clean_value($options, "user_id");

        if ($user_id) {
            $where .= " AND $wallets_table.user_id=$user_id";
        }

        $sql = "SELECT $wallets_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) as user_name
                FROM $wallets_table
                LEFT JOIN $users_table ON $users_table.id = $wallets_table.user_id
                WHERE $wallets_table.deleted=0 $where
                ORDER BY $wallets_table.created_at DESC";

        return $this->db->query($sql);
    }

    public function get_all_wallets() {
        $db = \Config\Database::connect();
        $wallets_table = $db->getPrefix() . $this->table;
        $users_table = $db->getPrefix() . 'users';

        $sql = "SELECT $wallets_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) as user_name
                FROM $wallets_table
                LEFT JOIN $users_table ON $users_table.id = $wallets_table.user_id
                WHERE $wallets_table.deleted=0
                ORDER BY $wallets_table.updated_at DESC";

        return $this->db->query($sql);
    }

    public function get_wallet_by_user($user_id) {
        $db = \Config\Database::connect();
        $table = $db->getPrefix() . $this->table;
        $sql = "SELECT * FROM $table
                WHERE user_id = ? AND deleted = 0";

        return $this->db->query($sql, array($user_id));
    }

    public function update_balance($wallet_id, $new_balance) {
        $data = array(
            "balance" => $new_balance,
            "updated_at" => get_current_utc_date_time()
        );

        return $this->ci_save($data, $wallet_id);
    }

    public function get_user_balance($user_id) {
        $wallet = $this->get_one_where(array(
            "user_id" => $user_id,
            "deleted" => 0
        ));

        if ($wallet && $wallet->id) {
            return $wallet->balance;
        }

        return 0;
    }
}
