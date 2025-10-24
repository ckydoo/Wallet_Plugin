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

            <div class="row mb-4">
                <div class="col-md-12">
                   <button type="button" class="btn btn-success" id="load-funds-btn">
                        <i data-feather='plus-circle' class='icon-16'></i> <?php echo wallet_lang('load_funds'); ?>
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4><?php echo wallet_lang('recent_transactions'); ?></h4>
                    <div class="table-responsive">
                        <table id="wallet-transactions-table" class="display" cellspacing="0" width="100%">
                        </table>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-info">
                <p><?php echo wallet_lang('wallet_not_created'); ?></p>
                <p><?php echo wallet_lang('contact_admin_to_create_wallet'); ?></p>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#load-funds-btn").click(function() {
            $.ajax({
                url: '<?php echo_uri("wallet_plugin/load_funds_modal"); ?>',
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
        
        $("#wallet-transactions-table").appTable({
            source: '<?php echo_uri("wallet_plugin/transaction_list_data"); ?>',
            columns: [
                {title: '<?php echo wallet_lang("date"); ?>'},
                {title: '<?php echo wallet_lang("type"); ?>'},
                {title: '<?php echo wallet_lang("amount"); ?>'},
                {title: '<?php echo wallet_lang("description"); ?>'},
                {title: '<?php echo wallet_lang("balance"); ?>'}
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