<div class="tab-content">
    <?php if (isset($wallet) && $wallet->id) { ?>
        <div class="wallet-info-section mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5><?php echo app_lang('wallet_balance'); ?></h5>
                            <h2 class="text-primary"><?php echo to_currency($wallet->balance, $wallet->currency); ?></h2>
                            <small class="text-muted">
                                <?php echo app_lang('last_updated') . ': ' . format_to_datetime($wallet->updated_at); ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <?php if ($can_manage_wallet) { ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><?php echo app_lang('actions'); ?></h5>
                            <?php 
                            echo modal_anchor(get_uri("wallet_plugin/load_funds_modal"), 
    "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_funds'), 
    array(
        "class" => "btn btn-success mb-2", 
        "title" => app_lang('add_funds'),
        "data-modal-lg" => true,
        "data-post-id" => "0"
    ));
                            ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="transactions-section">
            <h4><?php echo app_lang('wallet_transactions'); ?></h4>
            <div class="table-responsive">
                <table id="user-wallet-transactions-table" class="display" cellspacing="0" width="100%">
                </table>
            </div>
        </div>
    <?php } else { ?>
        <div class="alert alert-info">
            <p><?php echo app_lang('wallet_not_created'); ?></p>
            <?php if ($can_manage_wallet) { ?>
                <button class="btn btn-primary" id="create-wallet-btn">
                    <i data-feather="plus" class="icon-16"></i>
                    <?php echo app_lang('create_wallet'); ?>
                </button>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if (isset($wallet) && $wallet->id) { ?>
        $("#user-wallet-transactions-table").appTable({
            source: '<?php echo_uri("wallet_plugin/transaction_list_data"); ?>',
            filterParams: {user_id: <?php echo $user_id; ?>},
            columns: [
                {title: '<?php echo app_lang("date") ?>'},
                {title: '<?php echo app_lang("type") ?>'},
                {title: '<?php echo app_lang("amount") ?>'},
                {title: '<?php echo app_lang("description") ?>'},
                {title: '<?php echo app_lang("balance") ?>'}
            ],
            order: [[0, "desc"]]
        });
        <?php } ?>

        $("#create-wallet-btn").click(function() {
            // Implementation for creating wallet
            appAlert.info("<?php echo app_lang('feature_coming_soon'); ?>");
        });
    });
</script>
