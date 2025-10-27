<script type="text/javascript">
$(document).ready(function() {
    // Listen for payment method changes in the "Add payment" modal
    $(document).on('change', '#payment_method_id', function() {
        var paymentMethodId = $(this).val();
        var paymentMethodText = $(this).find('option:selected').text();
        
        // Check if "Wallet Payment" is selected
        if (paymentMethodText.toLowerCase().includes('wallet')) {
            // Get invoice ID and amount
            var invoiceId = $('input[name="invoice_id"]').val();
            var amount = $('input[name="payment_amount"]').val();
            
            if (invoiceId && amount) {
                checkClientWalletBalance(invoiceId, amount);
            }
        } else {
            // Remove any wallet balance warnings
            $('#wallet-balance-warning').remove();
        }
    });
    
    // Also check when amount changes
    $(document).on('keyup change', 'input[name="payment_amount"]', function() {
        var paymentMethodText = $('#payment_method_id').find('option:selected').text();
        
        if (paymentMethodText.toLowerCase().includes('wallet')) {
            var invoiceId = $('input[name="invoice_id"]').val();
            var amount = $(this).val();
            
            if (invoiceId && amount) {
                checkClientWalletBalance(invoiceId, amount);
            }
        }
    });
    
    function checkClientWalletBalance(invoiceId, amount) {
        $.ajax({
            url: '<?php echo_uri("wallet_plugin/check_client_balance"); ?>',
            type: 'POST',
            data: {
                invoice_id: invoiceId,
                amount: amount
            },
            dataType: 'json',
            success: function(response) {
                // Remove any existing warnings
                $('#wallet-balance-warning').remove();
                
                if (response.success) {
                    var warningHtml = '';
                    
                    if (response.has_sufficient_balance) {
                        warningHtml = `
                            <div id="wallet-balance-warning" class="alert alert-success mt-2">
                                <i class="fa fa-check-circle"></i> 
                                <strong>Client Wallet Balance: ${response.formatted_balance}</strong><br>
                                Sufficient balance available for this payment.
                            </div>
                        `;
                    } else {
                        warningHtml = `
                            <div id="wallet-balance-warning" class="alert alert-danger mt-2">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Insufficient Wallet Balance!</strong><br>
                                Client Balance: ${response.formatted_balance}<br>
                                Amount Required: ${response.formatted_amount_required}<br>
                                Please load funds into the client's wallet before processing this payment.
                            </div>
                        `;
                        
                        // Disable submit button
                        $('.modal-footer button[type="submit"]').prop('disabled', true);
                    }
                    
                    // Insert warning after payment method field
                    $('#payment_method_id').closest('.form-group').after(warningHtml);
                    
                    // Re-enable submit if balance is sufficient
                    if (response.has_sufficient_balance) {
                        $('.modal-footer button[type="submit"]').prop('disabled', false);
                    }
                } else {
                    var warningHtml = `
                        <div id="wallet-balance-warning" class="alert alert-warning mt-2">
                            <i class="fa fa-exclamation-circle"></i> 
                            ${response.message}
                        </div>
                    `;
                    $('#payment_method_id').closest('.form-group').after(warningHtml);
                }
            },
            error: function() {
                console.error('Error checking wallet balance');
            }
        });
    }
});
</script>