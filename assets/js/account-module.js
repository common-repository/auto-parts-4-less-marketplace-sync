/*
 * ==============================================
 * disable sync-quantity on disabling sync-orders
 * ==============================================
 */
jQuery(document).on('change', '#syncOrders', function() {
    if (jQuery(this).val() == 0) {
        jQuery('#syncQty option[value="0"]').prop('selected', true);
        jQuery('#syncQty option[value="1"]').prop('disabled', true);
    } else {
        jQuery('#syncQty option[value="1"]').prop('disabled', false);
    }
});

/*
 * ==============================================
 * active-deactive & sync-order & Sync-qty module
 * ==============================================
 */
jQuery(document).on('change', '.formEditMethod', function(e) {
    var $this = jQuery(this);
    var changeMethod = $this.attr('dataType');
    var accID = $this.attr('account_id');
    var successmsg = $this.attr('successmsg');
    var CheckBoxValue = $this.val();

    jQuery.ajax({
        url: ajaxUrl,
        method: 'POST',
        beforeSend: function() {},
        data: {
            security: ajaxNonce,
            action: pluginPrefix + 'accounts_action',
            changeMethod: changeMethod,
            successmsg: successmsg,
            accID: accID,
            CheckBoxValue: CheckBoxValue,
        },
        dataType: 'json',
    }).done(function(data) {
        jQuery('#alert div').html(data.message);

        if (data.status === 'success') {
            changeAlertToSuccess();
        } else {
            changeAlertToDanger();
        }

        closeAlertBox();
    });
});

/*
 * =====================
 * Account Delete button
 * =====================
 */
jQuery(document).on('click', '.accountDeleteBtn', function(e) {
    e.preventDefault();

    if (confirm('Are you sure you want to delete account?')) {
        var accID = jQuery(this).attr('data-id');

        if (accID) {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {},
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'accounts_delete',
                    accID: accID,
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('#alert div').html(data.message);

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    location.reload();
                } else {
                    changeAlertToDanger();
                }

                closeAlertBox();
            });
        }
    }
});

jQuery(document).ready(function($) {
    if (jQuery('#syncOrders').val() == 0) {
        jQuery('#syncQty option[value="0"]').prop('selected', true);
        jQuery('#syncQty option[value="1"]').prop('disabled', true);
    } else {
        jQuery('#syncQty option[value="1"]').prop('disabled', false);
    }

    /*
     * ==========================
     * Account Create/edit module
     * ==========================
     */
    jQuery("#AddAccoutForm").validate({
        rules: {
            accoutName: {
                required: true,
            },
            emailID: {
                required: true,
            },
            authToken: {
                required: true,
            },
        },
        messages: {
            accoutName: {
                required: 'Account Title is required.',
            },
            emailID: {
                required: 'Seller Email is required.',
            },
            authToken: {
                required: 'Auth Token is required.',
            },
        },
        submitHandler: function(form) {
            scrollToTop();

            jQuery('#AddAccoutForm [name="submit"]').attr('disabled', 'disabled');

            var formData = jQuery('#AddAccoutForm').serializeArray();

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'accounts_create',
                    formData: formData,
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('.loaderImage').hide();
                jQuery('#alert div').html(data.message);
                jQuery('#AddAccoutForm [name="submit"]').removeAttr('disabled');

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    var redUrl = adminUrl + 'admin.php?page=ap4l-accounts';
                    window.location.href = redUrl;
                } else {
                    changeAlertToDanger();
                }
            });
        }
    });
});

/*
 * ===============
 * close alert box
 * ===============
 */
function closeAlertBox() {
    setTimeout(function() {
        jQuery('.ap4lError').addClass('d-none');
    }, 2000);
}
