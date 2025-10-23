<div class="card">
    <div class="page-title clearfix">
        <h1><?php echo app_lang('manage_wallets'); ?></h1>
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
                {title: '<?php echo app_lang("user_name") ?>'},
                {title: '<?php echo app_lang("wallet_balance") ?>'},
                {title: '<?php echo app_lang("last_updated") ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>
