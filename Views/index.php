<div class="card">
    <div class="page-title clearfix">
        <h1><?php echo app_lang('wallet'); ?></h1>
    </div>

    <div class="card-body">
        <?php if (isset($wallet) && $wallet->id) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="wallet-balance-card bg-primary text-white p-4 rounded mb-4">
                        <h3 class="mb-2"><?php echo app_lang('wallet_balance'); ?></h3>
                        <h1 class="mb-0"><?php echo to_currency($wallet->balance, $wallet->currency); ?></h1>
                        <small><?php echo app_lang('last_updated') . ': ' . format_to_datetime($wallet->updated_at); ?></small>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <?php 
                    echo modal_anchor(get_uri("wallet_plugin/load_funds_modal"), 
                        "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('load_funds'), 
                        array("class" => "btn btn-success", "title" => app_lang('load_funds')));
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4><?php echo app_lang('recent_transactions'); ?></h4>
                    <div class="table-responsive">
                        <table id="wallet-transactions-table" class="display" cellspacing="0" width="100%">
                        </table>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-info">
                <p><?php echo app_lang('wallet_not_created'); ?></p>
                <p><?php echo app_lang('contact_admin_to_create_wallet'); ?></p>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#wallet-transactions-table").appTable({
            source: '<?php echo_uri("wallet_plugin/transaction_list_data"); ?>',
            columns: [
                {title: '<?php echo app_lang("date") ?>'},
                {title: '<?php echo app_lang("type") ?>'},
                {title: '<?php echo app_lang("amount") ?>'},
                {title: '<?php echo app_lang("description") ?>'},
                {title: '<?php echo app_lang("balance") ?>'}
            ],
            order: [[0, "desc"]]
        });
    });
</script>

<style>
    .wallet-balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
