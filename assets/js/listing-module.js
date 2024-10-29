/*
 * ====================
 * Listing add products
 * ====================
 */
jQuery(document).on('click', 'a.ajaxRed', function(e) {
    e.preventDefault();

    var $this = jQuery(this);
    var redURL = $this.attr('href');
    var listingID = $this.attr('listing-id');

    $this.find('i').toggleClass('dashicons-minus dashicons-plus');
    jQuery(document).find('.listingAction').removeClass('listingShow');

    jQuery.ajax({
        url: ajaxUrl,
        method: 'POST',
        beforeSend: function() {},
        data: {
            security: ajaxNonce,
            action: pluginPrefix + 'listing_product_cookie',
            listingID: listingID,
        },
        dataType: 'json',
    }).done(function(data) {
        jQuery('#alert div').html(data.message);

        if (data.status === 'success') {
            changeAlertToSuccess();
            window.location.href = redURL;
        } else {
            changeAlertToDanger();
        }

        closeAlertBox();
    });
});

/*
 * ==============
 * Delete Listing
 * ==============
 */
jQuery(document).on('click', '.listingDlt', function(e) {
    e.preventDefault();

    if (confirm('Are you sure you want to delete listing?')) {
        var $this = jQuery(this);
        var thisTr = $this.parents('tr');
        var LisID = thisTr.attr('listing-id');
        var LisID = $this.attr('listing-id');

        if (LisID) {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    thisTr.find('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'listing_delete',
                    LisID: LisID,
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('#alert div').html(data.message);
                thisTr.find('.loaderImage').hide();

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    thisTr.remove();
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
 * ======================
 * active deactive listing
 * ======================
 */
jQuery(document).on('change', '.listingStatusChange', function(e) {
    var $this = jQuery(this);
    var ListingID = $this.parents('td').attr('id');
    var ListingID = $this.attr('listing_id');
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
            action: pluginPrefix + 'listing_change',
            successmsg: successmsg,
            ListingID: ListingID,
            CheckBoxValue: CheckBoxValue,
        },
        dataType: 'json',
    }).done(function(data) {
        jQuery('#alert div').html(data.message);

        if (data.status === 'success') {
            changeAlertToSuccess();
        } else {
            $this.prop('checked', dashiconslse);
            changeAlertToDanger();
        }

        closeAlertBox();
    });
});

jQuery(document).ready(function($) {
    /*
     * ===============
     * New Listing Page
     * ===============
     */
    jQuery("#ap4lListingForm").validate({
        rules: {
            listingName: {
                required: true,
            },
            AccountId: {
                required: true,
            },
            SyncPolicy: {
                required: true,
            },
            ShippingPolicy: {
                required: true,
            },
            SellingPolicy: {
                required: true,
            },
        },
        messages: {
            listingName: {
                required: 'Listing Name is required.',
            },
            AccountId: {
                required: 'Account is required.',
            },
            SyncPolicy: {
                required: 'Synchronization Policy is required.',
            },
            ShippingPolicy: {
                required: 'Shipping Policy is required.',
            },
            SellingPolicy: {
                required: 'Selling Policy is required.',
            },
        },
        submitHandler: function(form) {
            scrollToTop();

            jQuery('#ap4lListingForm [name="submit"]').attr('disabled', 'disabled');
            var formData = jQuery('#ap4lListingForm').serializeArray();

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'listing_create',
                    formData: formData,
                },
                dataType: 'json',
            }).done(function(data) {
                jQuery('.loaderImage').hide();
                jQuery('#alert div').html(data.message);
                jQuery('#ap4lListingForm [name="submit"]').removeAttr('disabled');

                if (data.status === 'success') {
                    changeAlertToSuccess();
                    var redUrl = adminUrl + 'admin.php?page=ap4l-listings';
                    window.location.href = redUrl;
                } else {
                    changeAlertToDanger();
                }
            });
        }
    });
});
