<?php 
$target_user_id = isset($target_user_id) ? $target_user_id : 0;
$login_user_id = isset($login_user_id) ? $login_user_id : 0;
?>

<?php echo form_open(get_uri("wallet_plugin/add_funds"), array("id" => "wallet-load-funds-form", "class" => "general-form", "role" => "form")); ?>

<div class="form-group">
    <div class="row">
        <label for="target_user_id" class="col-md-3 col-sm-4">
            <?php echo app_lang('client'); ?> <span class="text-danger">*</span>
        </label>
        <div class="col-md-9 col-sm-8">
            <?php
            // Get list of all CLIENT USERS from users table (not clients table)
            $db = \Config\Database::connect();
            $db_prefix = $db->getPrefix();
            
            // Get client users from the users table
            $client_users = $db->table($db_prefix . 'users')
                ->where('deleted', 0)
                ->where('user_type', 'client')
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResult();
            
            // Build dropdown options
            $client_options = array("" => "- " . app_lang('select_a_client') . " -");
            
            if ($client_users) {
                foreach ($client_users as $user) {
                    // Format: First Name Last Name (Company)
                    $name = $user->first_name . ' ' . $user->last_name;
                    
                    // Get company name from clients table if available
                    if ($user->client_id) {
                        $client = $db->table($db_prefix . 'clients')
                            ->where('id', $user->client_id)
                            ->where('deleted', 0)
                            ->get()
                            ->getRow();
                        
                        if ($client && $client->company_name) {
                            $name .= ' (' . $client->company_name . ')';
                        }
                    }
                    
                    // Add email for clarity
                    if (!empty($user->email)) {
                        $name .= ' - ' . $user->email;
                    }
                    
                    $client_options[$user->id] = $name;
                }
            }
            
            echo form_dropdown(
                "target_user_id",
                $client_options,
                $target_user_id,
                array(
                    "id" => "target_user_id",
                    "class" => "form-control select2",
                    "required" => true,
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required")
                )
            );
            ?>
            <small class="form-text text-muted">Select the client user to load funds for</small>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <label for="amount" class="col-md-3 col-sm-4">
            <?php echo wallet_lang('amount'); ?> <span class="text-danger">*</span>
        </label>
        <div class="col-md-9 col-sm-8">
            <?php
            echo form_input(array(
                "id" => "amount",
                "name" => "amount",
                "value" => "",
                "class" => "form-control",
                "placeholder" => wallet_lang('amount'),
                "type" => "number",
                "step" => "0.01",
                "min" => "0.01",
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => app_lang("field_required"),
            ));
            ?>
            <small class="form-text text-muted">Enter the amount received from the client</small>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <label for="description" class="col-md-3 col-sm-4"><?php echo wallet_lang('description'); ?></label>
        <div class="col-md-9 col-sm-8">
            <?php
            echo form_textarea(array(
                "id" => "description",
                "name" => "description",
                "value" => "",
                "class" => "form-control",
                "placeholder" => wallet_lang('description') . ' (' . app_lang('optional') . ')',
                "rows" => 3
            ));
            ?>
            <small class="form-text text-muted">Optional: Add a note about this transaction (e.g., "Bank transfer ref #12345")</small>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i data-feather="info" class="icon-16"></i>
                <strong>Note:</strong> This will credit the selected client's wallet. Make sure you have received the payment from the client before loading funds.
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
        <span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?>
    </button>
    <button type="submit" class="btn btn-primary">
        <span data-feather="check-circle" class="icon-16"></span> <?php echo wallet_lang('add_funds'); ?>
    </button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        // Initialize Select2 for better client selection with search
        $("#target_user_id").select2({
            dropdownParent: $("#wallet-load-funds-modal")
        });
        
        $("#wallet-load-funds-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    $("#wallet-load-funds-modal").modal('hide');
                    appAlert.success(result.message, {duration: 10000});
                    
                    // Reload the page to show updated balance
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    appAlert.error(result.message);
                }
            }
        });
        
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>