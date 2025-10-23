<?php
// Get current user's wallet
$Wallet_model = new \Wallet_Plugin\Models\Wallet_model();
$login_user_id = $this->login_user->id ?? 0;

$wallet = null;
if ($login_user_id) {
    $wallet = $Wallet_model->get_one_where(array(
        "user_id" => $login_user_id,
        "deleted" => 0
    ));
}
?>

<?php if ($wallet && $wallet->id) { ?>
<div class="card dashboard-icon-widget">
    <div class="card-body">
        <div class="widget-icon bg-primary">
            <i data-feather="credit-card" class="icon"></i>
        </div>
        <div class="widget-details">
            <h1><?php echo to_currency($wallet->balance, $wallet->currency); ?></h1>
            <span class="bg-transparent-white"><?php echo app_lang("wallet_balance"); ?></span>
        </div>
        <a href="<?php echo get_uri('wallet_plugin/index'); ?>" class="widget-link">
            <i data-feather="circle" class="icon-16"></i> <?php echo app_lang("view_details"); ?>
        </a>
    </div>
</div>
<?php } ?>
