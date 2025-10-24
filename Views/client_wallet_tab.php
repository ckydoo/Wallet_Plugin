<div class="tab-content">
    <?php if (isset($wallet) && $wallet->id) { ?>
        <div class="wallet-info-section mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5><?php echo wallet_lang('wallet_balance'); ?></h5>
                            <h2 class="text-primary"><?php echo to_currency($wallet->balance, $wallet->currency); ?></h2>
                            <small class="text-muted">
                                <?php echo app_lang('last_updated') . ': ' . format_to_datetime($wallet->updated_at); ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($is_staff) && $is_staff) { ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><?php echo app_lang('actions'); ?></h5>
                            <?php 
                            echo modal_anchor(get_uri("wallet_plugin/load_funds_modal?target_user_id=" . $client_id), 
                                "<i data-feather='plus-circle' class='icon-16'></i> " . wallet_lang('load_funds'), 
                                array(
                                    "class" => "btn btn-success mb-2", 
                                    "title" => wallet_lang('load_funds'),
                                    "data-post-target_user_id" => $client_id
                                )
                            );
                            ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="transactions-section">
            <h4><?php echo wallet_lang('wallet_transactions'); ?></h4>
            <div class="table-responsive">
                <table id="client-wallet-transactions-table" class="display" cellspacing="0" width="100%">
                </table>
            </div>
        </div>
    <?php } else { ?>
        <div class="alert alert-warning">
            <p><?php echo wallet_lang('wallet_not_created'); ?></p>
            <?php if (isset($is_staff) && $is_staff) { ?>
                <?php 
                echo modal_anchor(get_uri("wallet_plugin/load_funds_modal?target_user_id=" . $client_id), 
                    "<i data-feather='plus-circle' class='icon-16'></i> " . wallet_lang('load_funds'), 
                    array(
                        "class" => "btn btn-primary", 
                        "title" => wallet_lang('load_funds'),
                        "data-post-target_user_id" => $client_id
                    )
                );
                ?>
                <p class="mt-2"><small>Creating a wallet and loading funds for this client</small></p>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if (isset($wallet) && $wallet->id) { ?>
        $("#client-wallet-transactions-table").appTable({
            source: '<?php echo_uri("wallet_plugin/transaction_list_data"); ?>',
            filterParams: {user_id: <?php echo $client_id; ?>},
            columns: [
                {title: '<?php echo app_lang("date"); ?>', "class": "w15p"},
                {title: '<?php echo wallet_lang("type"); ?>', "class": "w10p text-center"},
                {title: '<?php echo app_lang("amount"); ?>', "class": "w15p text-right"},
                {title: '<?php echo wallet_lang("description"); ?>'},
                {title: '<?php echo wallet_lang("balance_after"); ?>', "class": "w15p text-right"}
            ],
            order: [[0, "desc"]]
        });
        <?php } ?>

        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>