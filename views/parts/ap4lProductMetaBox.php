<?php
include_once AP4L_DIR . 'classes/UserModal.php';

use Ap4l\UserModal;

$UserModal            = new UserModal();
$getListing           = $UserModal->getListing();
$wcMappingAttrs       = $UserModal->getWCMappingAttributes();
/*
 * =====================
 * Get Old Value of meta
 * =====================
 */
$proId                = get_the_ID();
$ap4l_lising          = get_post_meta($proId, 'ap4l_pro_listing', true);
$ap4l_shipping_policy = get_post_meta($proId, 'ap4l_shipping_policyid', true);
?>
<div class="woocommerce_options_panel ap4lWcAttr">
    <p class="form-field">
        <label for="ap4lProListing">Select Listing:</label>
        <span class="woocommerce-help-tip" data-tip="AP4L Listing"></span>
        <select id="ap4lProListing" name="ap4lProListing" class="select short">
            <option value="">Select Listing</option>
            <?php foreach ($getListing as $key => $value) { ?>
                <option value="<?php echo esc_attr($value->id); ?>" <?php selected($ap4l_lising, $value->id); ?>><?php echo esc_html($value->listing_name); ?></option>
            <?php } ?>
        </select>
    </p>
    <p class="form-field">
        <label for="ap4l_shipping_policyid">Shipping Policy ID:</label>
        <span class="woocommerce-help-tip" data-tip="AP4L Shipping Policy ID"></span>
        <input type="text" id="ap4l_shipping_policyid" name="ap4l_shipping_policyid" value="<?php echo esc_attr(isset($ap4l_shipping_policy) ? $ap4l_shipping_policy : ''); ?>"/>
    </p>
    <?php
    /*
     * =============================================================
     * Generate Custom fields from WC mapping attribute table fields
     * =============================================================
     */
    if (! empty($wcMappingAttrs)) {
        foreach ($wcMappingAttrs as $map_key => $map_val) {
            $map_type     = $map_val->attribute_type;
            $map_name     = $map_val->attribute_name;
            $map_req      = $map_val->attribute_required;
            $map_des      = $map_val->attribute_desc;
            $map_des      = str_replace("'", '', $map_des);
            $map_slug     = 'ap4l-' . sanitize_title($map_val->attribute_name);
            $map_options  = $map_val->attribute_options;
            $meta_value   = get_post_meta($proId, $map_slug, true);
            $allowed_type = array( 'text', 'date' );
            if (in_array($map_type, $allowed_type)) {
                ?>
                <p class="form-field">
                    <label for="<?php echo esc_attr($map_slug); ?>">
                        <?php
                        echo wp_kses($map_name, AP4L_ALLOWED_HTML);
                        if ($map_req == 1) {
                            echo wp_kses("<span>*</span>", AP4L_ALLOWED_HTML);
                        }
                        ?>
                    </label>
                    <?php if (! empty($map_des)) { ?>
                        <span class="woocommerce-help-tip" data-tip='<?php echo esc_attr($map_des); ?>'></span>
                    <?php } ?>
                    <input type="<?php echo esc_attr($map_type); ?>" id="<?php echo esc_attr($map_slug); ?>" name="<?php echo esc_attr($map_slug); ?>" value="<?php echo esc_attr(isset($meta_value) ? $meta_value : ''); ?>"/>
                </p>
                <?php
            } elseif ($map_type == 'select' && ! empty($map_options)) {
                $map_options = explode('|', $map_options);
                ?>
                <p class="form-field">
                    <label for="<?php echo esc_attr($map_slug); ?>">
                        <?php
                        echo wp_kses($map_name, AP4L_ALLOWED_HTML);
                        if ($map_req == 1) {
                            echo wp_kses("<span>*</span>", AP4L_ALLOWED_HTML);
                        }
                        ?>
                    </label>
                    <?php if (! empty($map_des)) { ?>
                        <span class="woocommerce-help-tip" data-tip='<?php echo esc_attr($map_des); ?>'></span>
                    <?php } ?>
                    <select id="<?php echo esc_attr($map_slug); ?>" name="<?php echo esc_attr($map_slug); ?>" class="select short">
                        <option value="">Select</option>
                        <?php foreach ($map_options as $key => $value) { ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($meta_value, $value); ?>><?php echo esc_html($value); ?></option>
                        <?php } ?>
                    </select>
                </p>
                <?php
            }
        }
    }
    ?>
</div>