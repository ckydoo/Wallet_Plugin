<div class="card">
    <div class="page-title clearfix">
        <h1><?php echo app_lang('manage_wallets'); ?></h1>
        <div class="title-button-group">
            <button type="button" class="btn btn-success" id="load-funds-btn">
                <i data-feather="plus-circle" class="icon-16"></i> <?php echo wallet_lang('load_funds'); ?>
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="admin-wallets-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#admin-wallets-table").appTable({
            source: '<?php echo_uri("wallet_plugin/admin_wallet_list_data"); ?>',
            columns: [
                {title: '<?php echo app_lang("client"); ?>'},
                {title: '<?php echo wallet_lang("wallet_balance"); ?>', "class": "text-right w15p"},
                {title: '<?php echo app_lang("last_updated"); ?>', "class": "w20p"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            onInitComplete: function() {
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        });

        // Load funds button - opens modal with client selector
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

        // Handle load funds action from table row
        $(document).on('click', '.load-funds-action', function(e) {
            e.preventDefault();
            var clientId = $(this).data('client-id');
            var title = $(this).data('title');
            
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
                                            <h4 class="modal-title">${title}</h4>
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
    });
</script>