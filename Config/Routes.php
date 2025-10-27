<?php
namespace Config;

$routes = Services::routes();

// Main wallet routes
$routes->get('wallet_plugin', 'Wallet_Plugin::index', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/index', 'Wallet_Plugin::index', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/modal_form', 'Wallet_Plugin::modal_form', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/save', 'Wallet_Plugin::save', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/delete', 'Wallet_Plugin::delete', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/list_data', 'Wallet_Plugin::list_data', ['namespace' => 'Wallet_Plugin\Controllers']);

// Transaction routes - IMPORTANT: These must be BEFORE generic routes
$routes->get('wallet_plugin/transactions', 'Wallet_Plugin::transactions', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/transaction_details/(:num)', 'Wallet_Plugin::transaction_details/$1', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/transaction_list_data', 'Wallet_Plugin::transaction_list_data', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/transaction_list_data', 'Wallet_Plugin::transaction_list_data', ['namespace' => 'Wallet_Plugin\Controllers']);

// Load funds routes
$routes->get('wallet_plugin/load_funds_modal', 'Wallet_Plugin::load_funds_modal', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/add_funds', 'Wallet_Plugin::add_funds', ['namespace' => 'Wallet_Plugin\Controllers']);

// Payment routes
$routes->post('wallet_plugin/process_payment', 'Wallet_Plugin::process_payment', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/check_balance', 'Wallet_Plugin::check_balance', ['namespace' => 'Wallet_Plugin\Controllers']);

// Settings routes
$routes->get('wallet_plugin/settings', 'Wallet_Plugin::settings', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/save_settings', 'Wallet_Plugin::save_settings', ['namespace' => 'Wallet_Plugin\Controllers']);

// Client/User wallet routes
$routes->get('wallet_plugin/client_wallet/(:num)', 'Wallet_Plugin::client_wallet/$1', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/user_wallet/(:num)', 'Wallet_Plugin::user_wallet/$1', ['namespace' => 'Wallet_Plugin\Controllers']);

// Admin routes
$routes->get('wallet_plugin/admin_manage_wallets', 'Wallet_Plugin::admin_manage_wallets', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->get('wallet_plugin/admin_wallet_list_data', 'Wallet_Plugin::admin_wallet_list_data', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/admin_adjust_balance', 'Wallet_Plugin::admin_adjust_balance', ['namespace' => 'Wallet_Plugin\Controllers']);
// Balance check for admin
$routes->get('wallet_plugin/check_client_balance', 'Wallet_Plugin::check_client_balance', ['namespace' => 'Wallet_Plugin\Controllers']);
$routes->post('wallet_plugin/check_client_balance', 'Wallet_Plugin::check_client_balance', ['namespace' => 'Wallet_Plugin\Controllers']);
// Debug route (remove after testing)
$routes->get('wallet_plugin/debug_transactions', 'Wallet_Plugin::debug_transactions', ['namespace' => 'Wallet_Plugin\Controllers']);