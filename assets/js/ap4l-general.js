/*
 * ===================
 * Order Shipping Form
 * ===================
 */
jQuery(document).on('change', '#carrier_code', function() {
    if (jQuery(this).val() === 'Other') {
        jQuery('.otherChoice').show().addClass('required');
    } else {
        jQuery('.otherChoice').hide().removeClass('required');
    }
});

jQuery(document).on('click', '.ap4lAddShipping', function() {
    var FormFields = [];
    var ajaxAction = 1;

    jQuery(document).find('.ap4lOrdersShipping .form-field').each(function() {
        var $this = jQuery(this);
        var name = $this.find('input,select').attr('name');
        var value = $this.find('input,select').val();
        $this.removeClass('error');

        FormFields.push({ name: name, value: value });

        if ((value === '') && $this.hasClass('required')) {
            ajaxAction = 0;
            $this.addClass('error');
        }
    });

    if (ajaxAction) {
        // console.log(FormFields);

        jQuery.ajax({
            url: ajaxUrl,
            method: 'POST',
            beforeSend: function() {
                jQuery('.ap4lOrdersShipping .loaderImage').show();
            },
            data: {
                security: ajaxNonce,
                action: pluginPrefix + 'add_tracking_ap4l',
                FormFields: FormFields,
            },
            dataType: 'json',
        }).done(function(data) {
            jQuery('.ap4lOrdersShipping .loaderImage').hide();

            if (data.status === 'success') {
                jQuery('.responceMsg').addClass('success').removeClass('error').html(data.message);
            } else {
                jQuery('.responceMsg').addClass('error').removeClass('success').html(data.message);
            }
        });
    }

});

/*
 * ======================
 * Add listing to product
 * =======================
 */
jQuery(document).on('click', '.addProductListingBtn', function(e) {
    e.preventDefault();

    var selectedProducts = [];
    var listingIdJs = jQuery(this).attr('listid');

    jQuery("input[name='post[]']:checked").each(function() {
        selectedProducts.push(jQuery(this).val());
    });

    if (selectedProducts.length > 0) {
        jQuery.ajax({
            url: ajaxUrl,
            method: 'POST',
            beforeSend: function() {},
            data: {
                security: ajaxNonce,
                action: pluginPrefix + 'listing_product_add',
                selectedProducts: selectedProducts,
                listingIdJs: listingIdJs
            },
            dataType: 'json',
        }).done(function(data) {
            jQuery('#alert div').html(data.message);
            window.location.href = data.redUrl;
        });
    } else {
        alert('Please Select Products.');
    }
});

jQuery(document).ready(function($) {
    /*
     * ========================
     * Add button from cookie
     * =======================
     */
    if (jQuery('.edit-php.post-type-product').length > 0) {
        jQuery.ajax({
            url: ajaxUrl,
            method: 'POST',
            beforeSend: function() {},
            data: {
                security: ajaxNonce,
                action: pluginPrefix + 'listing_product_btn',
            },
            dataType: 'json',
        }).done(function(data) {
            if (data.status === 'success') {
                var appednHTML = '<a href="#" class="page-title-action addProductListingBtn" listid="' + data.listID + '">Add Product to ' + data.name + ' Listing</a>';
                jQuery('.edit-php.post-type-product').find('.page-title-action:last').after(appednHTML);
            }
        });
    }

    /*
     * ===================
     * Logs Module
     * ===================
     */
    jQuery("#LogsForm").validate({
        rules: {
            ap4l_logs_status: {
                required: true,
            }
        },
        messages: {
            ap4l_logs_status: {
                required: 'Select Log Status.'
            }
        },
        submitHandler: function(form) {
            var formData = jQuery('#LogsForm').serializeArray();

            // console.log(formData);

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'update_log_setting',
                    formData: formData,
                },
                dataType: 'json',
            }).done(function(data) {
                // console.log(data);

                jQuery('.loaderImage').hide();
                jQuery('#alert div').html(data.message);

                if (data.status === 'success') {
                    changeAlertToSuccess();
                } else {
                    changeAlertToDanger();
                }
            });
        }
    });
});

/*
 * =========================
 * scroll to top of the page
 * =========================
 */
function scrollToTop() {
    jQuery('html, body').animate({
        scrollTop: 0
    });
}

/*
 * ====================
 * remove alert classes
 * ====================
 */
function removeAlertClasses() {
    jQuery('#alert').removeClass('d-none alert-success alert-danger');
}

/*
 * ======================
 * change alert to danger
 * ======================
 */
function changeAlertToDanger() {
    removeAlertClasses();
    jQuery('#alert').addClass('alert-danger');
}

/*
 * =======================
 * change alert to success
 * =======================
 */
function changeAlertToSuccess() {
    removeAlertClasses();
    jQuery('#alert').addClass('alert-success');
}
