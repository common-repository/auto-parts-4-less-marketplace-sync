/*
 * ======================
 * active deactive policy
 * ======================
 */
jQuery(document).on('change', '.policyStatusChange', function(e) {
    var $this = jQuery(this);
    var PolicyID = $this.parents('td').attr('id');
    var successmsg = $this.attr('successmsg');
    var CheckBoxValue = 0;

    if ($this.is(":checked")) {
        CheckBoxValue = 1;
    } else {
        CheckBoxValue = 0;
    }

    jQuery.ajax({
        url: ajaxUrl,
        method: 'POST',
        beforeSend: function() {},
        data: {
            security: ajaxNonce,
            action: pluginPrefix + 'policy_change',
            successmsg: successmsg,
            PolicyID: PolicyID,
            CheckBoxValue: CheckBoxValue,
        },
        dataType: 'json',
    }).done(function(data) {
        jQuery('#alert div').html(data.message);

        if (data.status === 'success') {
            changeAlertToSuccess();
        } else {
            $this.prop('checked', false);
            changeAlertToDanger();
        }

        closeAlertBox();
    });
});

/*
 * =====================
 * General Delete Module
 * =====================
 */
jQuery(document).on('click', '.policyDlt', function(e) {
    e.preventDefault();

    if (confirm('Are you sure you want to delete policy?')) {
        scrollToTop();

        var $this = jQuery(this);
        var polID = $this.attr('policy-id');
        var polType = $this.attr('policy-type');

        if (polID && polType) {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {},
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'policy_delete',
                    polID: polID,
                    polType: polType,
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

/*
 * ===============
 * New Policy Page
 * ===============
 */
jQuery(document).on('change', '#policySelect', function() {
    var selectedPolicy = jQuery(this).val();
    var redUrl = adminUrl + 'admin.php?page=ap4l-policies&action=add&policy=' + selectedPolicy;
    window.location.href = redUrl;
});

/*
 * ==============
 * Selling Policy
 * ==============
 */
jQuery(document).on('change', '.attributeSelectionField', function() {
    var $this = jQuery(this);
    var otherOption = jQuery('#' + $this.attr('name') + '-other');

    if ($this.val() == 'staticvalue') {
        otherOption.show();
        otherOption.addClass('required');
    } else {
        otherOption.hide();
        otherOption.val('');
        otherOption.removeClass('required');
    }
});

jQuery(document).ready(function($) {
    /*
     * ===========
     * Sync Policy
     * ===========
     */
    jQuery("#SyncPolicyForm").validate({
        rules: {
            policyName: {
                required: true,
            }
        },
        messages: {
            policyName: {
                required: 'Policy Name is required.'
            }
        },
        submitHandler: function(form) {
            scrollToTop();

            jQuery('#SyncPolicyForm [name="submit"]').attr('disabled', 'disabled');
            var formData = jQuery('#SyncPolicyForm').serializeArray();

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'sync_policies_create',
                    formData: formData,
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('.loaderImage').hide();
                jQuery('#alert div').html(data.message);
                jQuery('#SyncPolicyForm [name="submit"]').removeAttr('disabled');

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    var redUrl = adminUrl + 'admin.php?page=ap4l-policies';
                    window.location.href = redUrl;
                } else {
                    changeAlertToDanger();
                }
            });
        }
    });

    /*
     * ===========
     * Shipping Policy
     * ===========
     */
    jQuery("#ShippingPolicyForm").validate({
        rules: {
            policyName: {
                required: true,
            },
            policyID: {
                required: true,
            },
        },
        messages: {
            policyName: {
                required: 'Policy Name is required.'
            },
            policyID: {
                required: 'AP4L Shipping Policy Id is required.'
            }
        },
        submitHandler: function(form) {
            scrollToTop();

            jQuery('#ShippingPolicyForm [name="submit"]').attr('disabled', 'disabled');
            var formData = jQuery('#ShippingPolicyForm').serializeArray();

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'ship_policies_create',
                    formData: formData,
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('.loaderImage').hide();
                jQuery('#alert div').html(data.message);
                jQuery('#ShippingPolicyForm [name="submit"]').removeAttr('disabled');

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    var redUrl = adminUrl + 'admin.php?page=ap4l-policies';
                    window.location.href = redUrl;
                } else {
                    changeAlertToDanger();
                }
            });
        }
    });

    jQuery("#SellingPolicyForm").validate({
        rules: {
            policyName: {
                required: true,
            },
            required: {
                required: true,
            }
        },
        messages: {
            policyName: {
                required: 'Policy Name is required.'
            },
            'general-wcAttr-0': {
                required: 'UPC is required.'
            },
            'general-wcAttr-1': {
                required: 'Product Brand is required.'
            },
            'general-wcAttr-2': {
                required: 'Part Number is required.'
            },
            'general-wcAttr-3': {
                required: 'Name is required.'
            },
            'general-wcAttr-5': {
                required: 'Product Weight is required.'
            },
            'general-wcAttr-12': {
                required: 'California Proposition65 Warn is required.'
            },
            'seller-wcAttr-0': {
                required: 'Vendor Sku is required.'
            },
            'seller-wcAttr-1': {
                required: 'Condition is required.'
            },
            'seller-wcAttr-3': {
                required: 'Price is required.'
            },
            'seller-wcAttr-7': {
                required: 'Quantity is required.'
            },
            'seller-wcAttr-9': {
                required: 'Handling Time No of Days is required.'
            },
            'seller-wcAttr-10': {
                required: 'Eligible For Return is required.'
            },
            'seller-wcAttr-11': {
                required: 'Return Within Days is required.'
            },
            'seller-wcAttr-12': {
                required: 'Allow Free Return is required.'
            },
            'seller-wcAttr-13': {
                required: 'Warranty Type is required.'
            },
            'seller-wcAttr-15': {
                required: 'Tax Category is required.'
            },
        },
        submitHandler: function(form) {
            scrollToTop();

            jQuery('#SellingPolicyForm [name="submit"]').attr('disabled', 'disabled');
            var formData = jQuery('#SellingPolicyForm').serializeArray();
            var attributeValues = [];

            jQuery('#SellingPolicyForm .SellerPolicyAttribute').each(function() {
                var $this = jQuery(this);
                var inputValue = $this.attr('id');
                var inputAttributeType = $this.data('attribute-type');
                var inputAttributeID = $this.data('ap4latrr-id');
                var inputAttributeValue = jQuery('#SellingPolicyForm select[name="' + inputValue + '"]').val();
                var OtherAttributeValue = '';

                $this.val(jQuery('#SellingPolicyForm select[name="' + inputValue + '"]').val());

                if (inputAttributeValue == 'staticvalue') {
                    OtherAttributeValue = jQuery('#SellingPolicyForm input[id="' + inputValue + '-other"]').val();
                    inputAttributeValue = '';
                }

                if (inputAttributeValue || OtherAttributeValue) {
                    attributeValues.push({ "type": inputAttributeType, "ap4l": inputAttributeID, "wc": inputAttributeValue, "staticvalue": OtherAttributeValue });
                }
            });

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'selling_policy_create',
                    formData: formData,
                    attributeValues: attributeValues
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('.loaderImage').hide();
                jQuery('#alert div').html(data.message);
                jQuery('#SellingPolicyForm [name="submit"]').removeAttr('disabled');

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    var redUrl = adminUrl + 'admin.php?page=ap4l-policies';
                    window.location.href = redUrl;
                } else {
                    changeAlertToDanger();
                }
            });
        }
    });
});
