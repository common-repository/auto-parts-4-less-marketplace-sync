jQuery(document).ready(function($) {
    jQuery('.catMapBoxSelect').select2({
        //        placeholder: 'Select AP4L Category'
    });

    jQuery('#CategoryForm select').first().focus();

    /*
     * ===================
     * Account Create/edit module
     * ===================
     */
    jQuery("#CategoryForm").validate({
        submitHandler: function(form) {
            scrollToTop();

            var attributeValues = [];

            jQuery('#CategoryForm .catMapBox').each(function() {
                var WooID = jQuery(this).val();
                var inputAttributeValue = jQuery('select[name="mapping' + WooID + '"]').val();

                // if (inputAttributeValue) {
                attributeValues.push({ "wooCat": WooID, "ap4l": inputAttributeValue });
                // }
            });

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                beforeSend: function() {
                    jQuery('.loaderImage').show();
                },
                data: {
                    security: ajaxNonce,
                    action: pluginPrefix + 'map_woo_cat',
                    attributeValues: attributeValues,
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
