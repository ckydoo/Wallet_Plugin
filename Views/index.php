<div class="card">
    <div class="page-title clearfix">
        <h1><?php echo wallet_lang('wallet'); ?></h1>
    </div>

    <div class="card-body">
        <?php if (isset($wallet) && $wallet->id) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="wallet-balance-card bg-primary text-white p-4 rounded mb-4">
                        <h3 class="mb-2"><?php echo wallet_lang('wallet_balance'); ?></h3>
                        <h1 class="mb-0"><?php echo to_currency($wallet->balance, $wallet->currency); ?></h1>
                        <small><?php echo wallet_lang('last_updated') . ': ' . format_to_datetime($wallet->updated_at); ?></small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mb-4">
                <i data-feather="info" class="icon-16"></i>
                <strong>How to add funds:</strong> To add funds to your wallet, please make a payment to our staff and request them to credit your wallet. You can then use these funds to pay for invoices.
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4 class="mb-3"><?php echo wallet_lang('recent_transactions'); ?></h4>
                    <div class="table-responsive">
                        <table id="wallet-transactions-table" class="display" cellspacing="0" width="100%">
                        </table>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-warning">
                <h4><i data-feather="alert-triangle" class="icon-16"></i> <?php echo wallet_lang('wallet_not_created'); ?></h4>
                <p>Your wallet has not been set up yet. Please contact our staff to create a wallet for you.</p>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if (isset($wallet) && $wallet->id) { ?>
        
        // Initialize the transactions table
        $("#wallet-transactions-table").appTable({
            source: '<?php echo_uri("wallet_plugin/transaction_list_data"); ?>',
            columns: [
                {title: '<?php echo wallet_lang("date"); ?>', "class": "w20p"},
                {title: '<?php echo wallet_lang("type"); ?>', "class": "w10p text-center"},
                {title: '<?php echo wallet_lang("amount"); ?>', "class": "w15p text-right"},
                {title: '<?php echo wallet_lang("description"); ?>', "class": "w40p"},
                {title: '<?php echo wallet_lang("balance_after"); ?>', "class": "w15p text-right"}
            ],
            order: [[0, "desc"]],
            printColumns: [0, 1, 2, 3, 4],
            xlsColumns: [0, 1, 2, 3, 4]
        });
        
        <?php } ?>
        
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

<style>
    .wallet-balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>