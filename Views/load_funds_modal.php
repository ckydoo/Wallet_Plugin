<?php echo form_open(get_uri("wallet_plugin/add_funds"), array("id" => "wallet-load-funds-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <div class="form-group">
            <label for="amount" class="col-md-3"><?php echo app_lang('amount'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "amount",
                    "name" => "amount",
                    "value" => "",
                    "class" => "form-control",
                    "placeholder" => app_lang('amount'),
                    "type" => "number",
                    "step" => "0.01",
                    "min" => "0.01",
                    "autofocus" => true,
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-md-3"><?php echo app_lang('description'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_textarea(array(
                    "id" => "description",
                    "name" => "description",
                    "value" => "",
                    "class" => "form-control",
                    "placeholder" => app_lang('description'),
                    "rows" => 3
                ));
                ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <p class="text-muted">
                    <i data-feather="info" class="icon-16"></i>
                    <?php echo app_lang('wallet_load_funds_note'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('add_funds'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#wallet-load-funds-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                    location.reload();
                } else {
                    appAlert.error(result.message);
                }
            }
        });
    });
</script>
