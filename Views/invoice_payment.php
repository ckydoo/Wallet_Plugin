<?php
$payment_method = get_array_value($payment_method_variables, "payment_method");
$balance_due = get_array_value($payment_method_variables, "balance_due");
$currency = get_array_value($payment_method_variables, "currency");
$invoice_id = get_array_value($payment_method_variables, "invoice_id");
$invoice_info = get_array_value($payment_method_variables, "invoice_info");

$pay_button_text = get_setting("wallet_payment_pay_button_text") ?: "Pay from Wallet";
$wallet_description = get_setting("wallet_payment_wallet_description") ?: "Use your wallet balance to make this payment";
?>

<div class="payment-method-section wallet-payment-section mb-3">
    <h5><?php echo $pay_button_text; ?></h5>
    <p class="text-muted"><?php echo $wallet_description; ?></p>
    
    <div class="wallet-info mb-3">
        <div class="row">
            <div class="col-md-6">
                <strong><?php echo app_lang('wallet_balance'); ?>:</strong>
                <span id="wallet-balance-display" class="text-primary">
                    <?php echo app_lang('loading'); ?>
                </span>
            </div>
            <div class="col-md-6">
                <strong><?php echo app_lang('amount_to_pay'); ?>:</strong>
                <span class="text-success">
                    <?php echo to_currency($balance_due, $currency); ?>
                </span>
            </div>
        </div>
    </div>

    <div id="wallet-insufficient-balance" class="alert alert-warning" style="display: none;">
        <?php echo app_lang('insufficient_wallet_balance'); ?>
        <?php 
        echo modal_anchor(get_uri("wallet_plugin/load_funds_modal"), 
            app_lang('load_funds'), 
            array("class" => "btn btn-sm btn-primary"));
        ?>
    </div>

    <button type="button" id="wallet-pay-button" class="btn btn-primary" style="display: none;">
        <i data-feather="credit-card" class="icon-16"></i>
        <?php echo $pay_button_text; ?>
    </button>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Check wallet balance
        $.ajax({
            url: '<?php echo_uri("wallet_plugin/check_balance"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#wallet-balance-display").html(response.formatted_balance);
                    
                    var balance = parseFloat(response.balance);
                    var amountToPay = parseFloat(<?php echo $balance_due; ?>);
                    
                    if (balance >= amountToPay) {
                        $("#wallet-pay-button").show();
                    } else {
                        $("#wallet-insufficient-balance").show();
                    }
                } else {
                    $("#wallet-balance-display").html('<span class="text-danger">' + response.message + '</span>');
                }
            },
            error: function() {
                $("#wallet-balance-display").html('<span class="text-danger"><?php echo app_lang("error_occurred"); ?></span>');
            }
        });

        // Handle payment button click
        $("#wallet-pay-button").click(function() {
            if (confirm('<?php echo app_lang("confirm_wallet_payment"); ?>')) {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo app_lang("processing"); ?>');

                $.ajax({
                    url: '<?php echo_uri("wallet_plugin/process_payment"); ?>',
                    type: 'POST',
                    data: {
                        invoice_id: <?php echo $invoice_id; ?>,
                        amount: <?php echo $balance_due; ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            appAlert.success(response.message, {duration: 5000});
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            appAlert.error(response.message);
                            $btn.prop('disabled', false).html('<i data-feather="credit-card" class="icon-16"></i> <?php echo $pay_button_text; ?>');
                        }
                    },
                    error: function() {
                        appAlert.error('<?php echo app_lang("error_occurred"); ?>');
                        $btn.prop('disabled', false).html('<i data-feather="credit-card" class="icon-16"></i> <?php echo $pay_button_text; ?>');
                    }
                });
            }
        });
    });
</script>

<style>
    .wallet-payment-section {
        border: 1px solid #e0e0e0;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    
    .wallet-info {
        background-color: #fff;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #667eea;
    }
</style>
