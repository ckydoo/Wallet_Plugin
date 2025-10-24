<?php

namespace Wallet_Plugin\Models;

use App\Models\Crud_model;

class Wallet_settings_model extends Crud_model {

    protected $table = null;
    protected $allowedFields = [
        'setting_name',
        'setting_value',
        'deleted'
    ];

    public function __construct() {
        $this->table = 'wallet_settings';
        parent::__construct($this->table);
    }

    public function get_setting($setting_name) {
        $result = $this->get_one_where(array(
            "setting_name" => $setting_name,
            "deleted" => 0
        ));

        if ($result && $result->id) {
            return $result->setting_value;
        }

        return "";
    }

    public function save_setting($setting_name, $setting_value) {
        $exists = $this->get_one_where(array(
            "setting_name" => $setting_name
        ));

        $data = array(
            "setting_name" => $setting_name,
            "setting_value" => $setting_value
        );

        if ($exists && $exists->id) {
            return $this->update($exists->id, $data);
        } else {
            return $this->insert($data);
        }
    }

    public function get_all_settings() {
        $settings = $this->get_all_where(array("deleted" => 0))->getResult();
        
        $settings_array = array();
        foreach ($settings as $setting) {
            $settings_array[$setting->setting_name] = $setting->setting_value;
        }

        return $settings_array;
    }
}