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
                            <button type="button" class="btn btn-success mb-2" id="load-funds-for-client-btn" data-client-id="<?php echo $client_id; ?>">
                                <i data-feather='plus-circle' class='icon-16'></i> <?php echo wallet_lang('load_funds'); ?>
                            </button>
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
                <button class="btn btn-primary" id="load-funds-for-client-btn" data-client-id="<?php echo $client_id; ?>">
                    <i data-feather="plus-circle" class="icon-16"></i>
                    <?php echo wallet_lang('load_funds'); ?>
                </button>
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
                {title: '<?php echo app_lang("type"); ?>', "class": "w10p text-center"},
                {title: '<?php echo app_lang("amount"); ?>', "class": "w15p text-right"},
                {title: '<?php echo app_lang("description"); ?>'},
                {title: '<?php echo wallet_lang("balance_after"); ?>', "class": "w15p text-right"}
            ],
            order: [[0, "desc"]]
        });
        <?php } ?>

        // Load funds button for staff
        $(document).on('click', '#load-funds-for-client-btn', function() {
            var clientId = $(this).data('client-id');
            
            $.ajax({
                url: '<?php echo_uri("wallet_plugin/load_funds_modal"); ?>?target_user_id=' + clientId,
                type: 'GET',
                dataType: 'html',
                success: function(result) {
                    if (result && result.trim() !== '') {
                        var modalContent = `
                            <div class="modal fade" id="wallet-load-funds-modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title"><?php echo wallet_lang("load_funds"); ?></h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${result}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        $("#wallet-load-funds-modal").remove();
                        $("body").append(modalContent);
                        $("#wallet-load-funds-modal").modal('show');
                        
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    } else {
                        appAlert.error('<?php echo app_lang("error_occurred"); ?>');
                    }
                },
                error: function(xhr, status, error) {
                    appAlert.error('<?php echo app_lang("error_occurred"); ?>');
                }
            });
        });

        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>