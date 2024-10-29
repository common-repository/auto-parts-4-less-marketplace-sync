<?php
/*
 * ================
 * Define variables
 * ================
 */

global $wpdb;

define('AP4L_BASE', plugin_basename(__FILE__));
define('AP4L_URL', plugin_dir_url(__DIR__));
define('AP4L_VERSION', '1.0.0');
define('AP4L_PLUGIN_NAME', 'Auto Parts 4 Less Marketplace Sync');
define('AP4L_PLUGIN_SLUG', 'auto-parts-4-less-marketplace-sync');
define('AP4L_NAME', 'Auto Parts 4 Less');
define('AP4L_SLUG', 'ap4l');
define('AP4L_FOLDER', 'ap4l');
define('AP4L_PREFIX', 'ap4l_');
define('AP4L_TIMEZONE', wp_timezone_string());
define('AP4L_PRODUCT_POSTTYPE', 'product');
define('AP4L_TABLE_PREFIX', $wpdb->prefix . 'ap4l_');
define('AP4L_API_URL', 'https://sellers-api.autoparts4less.com/v1/');
define('AP4L_PRODUCT_META_AP4L_UPC', 'ap4l_product_upc');
define('AP4L_PRODUCT_SYNCED', 'ap4l_pro_synced');
define('AP4L_PRODUCT_EXIST_IN_AP4L', 'ap4l_pro_exists_ap4l');
define('AP4L_CATEGORY_KEY', 'ap4l_cat_id');
define('AP4L_PRODUCT_QUEUE_ID', 'ap4l_pro_queue_id');
define('AP4L_PRODUCT_NEEDS_UPDATE', 'ap4l_pro_needs_update');
define('AP4L_QUEUE_LOG_ID', 'ap4l_queue_log_id');
define('AP4L_QUEUE_TYPE', 'ap4l_queue_type');
define('AP4L_PRODUCT_META_AP4L_VENDOR_SKU', 'ap4l_product_vendor_sku');
define('AP4L_ACCOUNT_URL', admin_url() . 'admin.php?page=ap4l-accounts');
define('AP4L_POLICY_URL', admin_url() . 'admin.php?page=ap4l-policies');
define('AP4L_LISTING_URL', admin_url() . 'admin.php?page=ap4l-listings');
define('AP4L_LISTING_INNER_URL', admin_url() . 'admin.php?page=ap4l-listings-inner');
define('AP4L_ORDERS_URL', admin_url() . 'admin.php?page=ap4l-orders');
define('AP4L_ORDERS_LOGS_URL', admin_url() . 'admin.php?page=ap4l-orders-logs');
define('AP4L_LISTING_LOGS_URL', admin_url() . 'admin.php?page=ap4l-listing-logs');
define('AP4L_PER_PAGE_ORDERS', 30);
define('AP4L_LOG_STATUS', true);

$ap4l_logs_days   = get_option('ap4l_logs_days');
$ap4l_logs_days   = (!empty(intval($ap4l_logs_days))) ? intval($ap4l_logs_days) : 60;
define('AP4L_LOGS_DAYS', $ap4l_logs_days);

$allowed_html_post = wp_kses_allowed_html('post');
$allowed_html_form = array(
    'input' => array(
        'name' => true,
        'id' => true,
        'class' => true,
        'title' => true,
        'style' => true,
        'disabled' => true,
        'checked' => true,
        'type' => true,
        'value' => true,
        'placeholder' => true,
    ),
    'select' => array(
        'name' => true,
        'id' => true,
        'class' => true,
        'title' => true,
        'style' => true,
        'disabled' => true,
        'multiple' => true,
    ),
    'option' => array(
        'name' => true,
        'id' => true,
        'class' => true,
        'title' => true,
        'style' => true,
        'disabled' => true,
        'value' => true,
        'selected' => true,
    ),
);

$allowed_html = array_merge($allowed_html_post, $allowed_html_form);
define('AP4L_ALLOWED_HTML', $allowed_html);

if (!function_exists('ap4l_sanitize_array')) {
    function ap4l_sanitize_array($array)
    {
        if (is_array($array)) {
            array_walk($array, function (&$value, $key) {
                if (is_array($value)) {
                    $value = ap4l_sanitize_array($value);
                } else {
                    $value = sanitize_text_field($value);
                }
            });
        }

        return $array;
    }
}
