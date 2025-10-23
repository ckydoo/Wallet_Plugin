<div class="card">
    <div class="page-title clearfix">
        <h1><?php echo app_lang('wallet_settings'); ?></h1>
    </div>

    <div class="card-body">
        <?php echo form_open(get_uri("wallet_plugin/save_settings"), array("id" => "wallet-settings-form", "class" => "general-form", "role" => "form")); ?>
        
        <div class="form-group">
            <label for="wallet_enabled" class="col-md-3"><?php echo app_lang('enable_wallet'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_checkbox("wallet_enabled", "1", 
                    get_array_value($wallet_settings, "wallet_enabled") == "1" ? true : false, 
                    "id='wallet_enabled' class='form-check-input'");
                ?>
                <span class="form-check-label"><?php echo app_lang('enable_wallet_system'); ?></span>
            </div>
        </div>

        <div class="form-group">
            <label for="auto_create_wallet" class="col-md-3"><?php echo app_lang('auto_create_wallet'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_checkbox("auto_create_wallet", "1", 
                    get_array_value($wallet_settings, "auto_create_wallet") == "1" ? true : false, 
                    "id='auto_create_wallet' class='form-check-input'");
                ?>
                <span class="form-check-label"><?php echo app_lang('auto_create_wallet_for_users'); ?></span>
            </div>
        </div>

        <div class="form-group">
            <label for="wallet_currency" class="col-md-3"><?php echo app_lang('wallet_currency'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "wallet_currency",
                    "name" => "wallet_currency",
                    "value" => get_array_value($wallet_settings, "wallet_currency") ?: "USD",
                    "class" => "form-control",
                    "placeholder" => "USD"
                ));
                ?>
                <small class="form-text text-muted"><?php echo app_lang('default_currency_for_wallets'); ?></small>
            </div>
        </div>

        <div class="form-group">
            <label for="minimum_balance" class="col-md-3"><?php echo app_lang('minimum_balance'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "minimum_balance",
                    "name" => "minimum_balance",
                    "value" => get_array_value($wallet_settings, "minimum_balance") ?: "0.00",
                    "class" => "form-control",
                    "type" => "number",
                    "step" => "0.01",
                    "min" => "0"
                ));
                ?>
                <small class="form-text text-muted"><?php echo app_lang('minimum_balance_required'); ?></small>
            </div>
        </div>

        <div class="form-group">
            <label for="allow_negative_balance" class="col-md-3"><?php echo app_lang('allow_negative_balance'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_checkbox("allow_negative_balance", "1", 
                    get_array_value($wallet_settings, "allow_negative_balance") == "1" ? true : false, 
                    "id='allow_negative_balance' class='form-check-input'");
                ?>
                <span class="form-check-label"><?php echo app_lang('allow_users_to_have_negative_balance'); ?></span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?>
                </button>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#wallet-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }
            }
        });
    });
</script>
