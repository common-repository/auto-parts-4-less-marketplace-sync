<?php
/**
 * This class defines all code necessary to run during the plugin's activation.
 */
namespace Ap4l;

include_once AP4L_DIR . 'classes/Ajax.php';
include_once AP4L_DIR . 'classes/CronJobs.php';
include_once AP4L_DIR . 'classes/Admin.php';

use Ap4l\Ajax;
use Ap4l\CronJobs;
use Ap4l\Admin;

if (! class_exists('Main')) {
    class Main extends Admin
    {
        /**
         * ===========================================
         * Define the core functionality of the plugin.
         * ===========================================
         */
        public function __construct()
        {
            parent::__construct();
            $this->defineAdminHooks();
            $this->createProductListing();
            $this->createOrdersFields();
            $this->createCronJob();
            $this->createPaymentShipping();
        }
        /**
         * ================================================================================
         * Register all of the hooks related to the admin area functionality of the plugin.
         * ================================================================================
         */
        public function add_options_accounts()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'accounts_per_page'
            );
            add_screen_option($option, $args);
        }
        public function add_options_policies()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'policies_per_page'
            );
            add_screen_option($option, $args);
        }
        public function add_options_listing()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'listings_per_page'
            );
            add_screen_option($option, $args);
        }
        public function add_options_listing_inner()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'listings_inner_per_page'
            );
            add_screen_option($option, $args);
        }
        public function add_options_orders()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'orders_per_page'
            );
            add_screen_option($option, $args);
        }
        public function add_options_orders_log()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'orders_log_per_page'
            );
            add_screen_option($option, $args);
        }
        public function add_options_listing_log()
        {
            $option = 'per_page';
            $args   = array(
                'label'   => 'Results',
                'default' => 10,
                'option'  => 'listing_log_per_page'
            );
            add_screen_option($option, $args);
        }

        function set_option($status, $option, $value)
        {
            if (isset($_REQUEST['wp_screen_options_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['wp_screen_options_nonce'])), 'wp_screen_options_nonce')) {
                if ('accounts_per_page' === $option) {
                    if (!empty($_REQUEST['accounts_per_page'])) {
                        if (is_array($_REQUEST['accounts_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['accounts_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['accounts_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }

                if ('policies_per_page' === $option) {
                    if (!empty($_REQUEST['policies_per_page'])) {
                        if (is_array($_REQUEST['policies_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['policies_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['policies_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }

                if ('listings_per_page' === $option) {
                    if (!empty($_REQUEST['listings_per_page'])) {
                        if (is_array($_REQUEST['listings_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['listings_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['listings_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }

                if ('listings_inner_per_page' === $option) {
                    if (!empty($_REQUEST['listings_inner_per_page'])) {
                        if (is_array($_REQUEST['listings_inner_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['listings_inner_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['listings_inner_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }

                if ('listing_log_per_page' === $option) {
                    if (!empty($_REQUEST['listing_log_per_page'])) {
                        if (is_array($_REQUEST['listing_log_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['listing_log_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['listing_log_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }

                if ('orders_per_page' === $option) {
                    if (!empty($_REQUEST['orders_per_page'])) {
                        if (is_array($_REQUEST['orders_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['orders_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['orders_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }

                if ('orders_log_per_page' === $option) {
                    if (!empty($_REQUEST['orders_log_per_page'])) {
                        if (is_array($_REQUEST['orders_log_per_page'])) {
                            $value = ap4l_sanitize_array($_REQUEST['orders_log_per_page']);
                        } else {
                            $value = sanitize_text_field($_REQUEST['orders_log_per_page']);
                        }
                    } else {
                        $value = [];
                    }
                }
            }

            return $value;
        }
        public function defineAdminHooks()
        {
            /*
             * ===========
             * Enable Logs
             * ===========
             */
            if (defined('WP_DEBUG') && !empty(WP_DEBUG)) {
                define('AP4L_LOG_FILE', AP4L_DIR . 'logs/' . date('Ymd') . '.log');
                ini_set('error_log', AP4L_LOG_FILE);
            }

            /*
             * ==================
             * Plugin action link
             * ==================
             */
            add_filter('plugin_action_links_' . AP4L_FOLDER . '/ap4l.php', array( $this, 'ap4l_plugin_action_links' ));
            /*
             * ============
             * Add CSS & JS
             * ============
             */
            add_action('admin_enqueue_scripts', array( $this, 'ap4lEnqueueStyles' ), 999);
            add_action('admin_enqueue_scripts', array( $this, 'ap4lEnqueueScripts' ), 999);
            /*
             * ===============
             * AP4L Admin Menu
             * ===============
             */
            add_action('admin_menu', array( $this, 'ap4lAddSettingsPage' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-accounts", array( $this, 'add_options_accounts' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-policies", array( $this, 'add_options_policies' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-listings", array( $this, 'add_options_listing' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-listings-inner", array( $this, 'add_options_listing_inner' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-listing-logs", array( $this, 'add_options_listing_log' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-orders", array( $this, 'add_options_orders' ));
            add_action("load-".AP4L_PLUGIN_SLUG."_page_ap4l-orders-logs", array( $this, 'add_options_orders_log' ));
            add_filter('set-screen-option', array( $this, 'set_option' ), 10, 3);
            /*
             * ===========
             * Ajax Object
             * ===========
             */
            $ajaxObj = new Ajax();
            /*
             * ==============
             * Account Action
             * ==============
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'accounts_create', array( $ajaxObj, 'accountsCreateAction' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'accounts_delete', array( $ajaxObj, 'accountsDeleteAction' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'accounts_action', array( $ajaxObj, 'accountsChnageAction' ));
            /*
             * ============================
             * General Policy View & Delete Action
             * ============================
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'policy_delete', array( $ajaxObj, 'AllPolicyDelete' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'policy_change', array( $ajaxObj, 'AllPolicyChange' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'sync_policies_create', array( $ajaxObj, 'SyncPolicyCreate' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'ship_policies_create', array( $ajaxObj, 'ShippingPolicyCreate' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'selling_policy_create', array( $ajaxObj, 'SellingPolicyCreate' ));
            /*
             * =====================
             * Listing Action
             * =====================
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'listing_create', array( $ajaxObj, 'ListingCreate' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'listing_delete', array( $ajaxObj, 'ListingDelete' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'listing_change', array( $ajaxObj, 'ListingChange' ));
            /*
             * =====================
             * Listing Cookie
             * ====================
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'listing_product_cookie', array( $ajaxObj, 'ListingProductCookie' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'listing_product_btn', array( $ajaxObj, 'ListingProductBtn' ));
            add_action('wp_ajax_' . AP4L_PREFIX . 'listing_product_add', array( $ajaxObj, 'ListingProductAdd' ));
            /*
             * ========================
             * Add Category Mapping action
             * ========================
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'map_woo_cat', array( $ajaxObj, 'AddCategoryMapping' ));
            /*
             * ========================
             * Add Logs Mapping action
             * ========================
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'update_log_setting', array( $ajaxObj, 'UpdateLogsSetting' ));
            /*
             * ========================
             * Add Tracking Info action
             * ========================
             */
            add_action('wp_ajax_' . AP4L_PREFIX . 'add_tracking_ap4l', array( $ajaxObj, 'AddTrackingOrderAP4L' ));
            /*
             * ================================
             * Sync Action Product add & Update
             * ================================
             */
            add_action('wp_after_insert_post', array( $this, 'SyncProductWithAP4L' ), 10, 3);
            /*
             * ===================
             * Admin Notice action
             * ===================
             */
            add_action('admin_notices', array( $this, 'display_ap4l_function_notices' ), 12);
            add_action('woocommerce_thankyou', array( $this, 'after_ap4l_order_fetched' ), 10, 1);
        }
        /*
         * ========================
         * Create Product Meta Box
         * =======================
         */
        public function createProductListing()
        {
            /**
             * Register meta box.
             */
            add_action('add_meta_boxes', array( $this, 'ap4l_product_listing_meta' ));
            add_action('save_post_' . AP4L_PRODUCT_POSTTYPE, array( $this, 'ap4l_product_save_action' ), 99999999, 3);
            /*
             * Product Page Filter
             */
            add_action('restrict_manage_posts', array( $this, 'ap4l_product_storage_filter' ), 10, 2);
            add_filter('parse_query', array( $this, 'ap4l_product_storage_filter_query' ), 1000);
            /*
             * Product Bulk Edit
             */
            add_action('woocommerce_product_bulk_edit_start', array( $this, 'ap4l_product_listing_bulk_edit' ), 10, 0);
            add_action('woocommerce_product_bulk_edit_save', array( $this, 'ap4l_product_listing_bulk_edit_save' ), 10, 1);
            /*
             * Product Quick Edit
             */
            add_action('woocommerce_product_quick_edit_start', array( $this, 'ap4l_product_listing_quick_edit' ), 10, 0);
            add_action('woocommerce_product_quick_edit_save', array( $this, 'ap4l_product_listing_quick_edit_Save' ), 10, 1);
            /*
             * Product Column add
             */
            add_filter('manage_edit-product_columns', array( $this, 'ap4l_products_listing_column' ), 999);
            add_action('manage_product_posts_custom_column', array( $this, 'ap4l_products_listing_column_content' ), 10, 2);
            add_filter('manage_edit-product_sortable_columns', array( $this, 'ap4l_product_sortable_columns' ), 999);
            add_filter('default_hidden_columns', array( $this, 'ap4l_default_hidden_columns' ), 20, 2);
        }
        /*
         * ====================================
         * Create a Woocommerce MetaBox & Query
         * ===================================
         */
        public function createOrdersFields()
        {
            /*
             * WooCommerce Add Code
             */
            add_action('woocommerce_admin_order_data_after_order_details', array( $this, 'add_ap4l_order_status_to_order' ), 10, 1);
            add_filter('woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'ap4l_custom_query_var' ), 10, 2);
            /*
             * Register Meta Box
             */
            add_action('add_meta_boxes', array( $this, 'ap4l_orders_shipping_meta' ));
            add_action('save_post_shop_order', array( $this, 'ap4l_orders_save_action' ), 10, 3);
        }
        /*
         * ====================================
         * Create a cron job of every 5 minute
         * ===================================
         */
        public function createCronJob()
        {
            $cronObj = new CronJobs();
            add_filter('cron_schedules', array( $cronObj, 'ap4l_add_time_schedule_cron' ));

            // Main action triger every minute
            if (! wp_next_scheduled('ap4l_queue_status_check_cron')) {
                wp_schedule_event(time(), 'ap4l_1_minute', 'ap4l_queue_status_check_cron');
            }

            if (! wp_next_scheduled('ap4l_main_five_min_cron_action')) {
                wp_schedule_event(time(), 'ap4l_1_minute', 'ap4l_main_five_min_cron_action');
            }

            // runs daily
            if (! wp_next_scheduled('ap4l_main_daily_cron_action')) {
                wp_schedule_event(time(), 'ap4l_once_daily', 'ap4l_main_daily_cron_action');
            }

            if (! wp_next_scheduled('ap4l_quantity_ap4l_to_wp')) {
                wp_schedule_event(time(), 'ap4l_1_minute', 'ap4l_quantity_ap4l_to_wp');
            }

            /*
             * Sub Cron Action
             */
            add_action('ap4l_main_five_min_cron_action', array( $cronObj, 'ap4l_get_order_from_api' ));
            add_action('ap4l_main_five_min_cron_action', array( $cronObj, 'ap4l_old_orders_cron' ));
            add_action('ap4l_main_five_min_cron_action', array( $cronObj, 'ap4l_product_cron' ));
            add_action('ap4l_main_five_min_cron_action', array( $cronObj, 'ap4l_product_update_cron' ));
            add_action('ap4l_queue_status_check_cron', array( $cronObj, 'ap4l_queue_status_check_cron' ));
            add_action('ap4l_main_daily_cron_action', array( $cronObj, 'ap4l_delete_logs_cron' ));
            add_action('ap4l_quantity_ap4l_to_wp', array( $cronObj, 'ap4l_quantity_ap4l_to_wp' ));
        }
        /*
         * ====================================
         * Create Payment & shipping Methods
         * ===================================
         */
        public function createPaymentShipping()
        {
            /*
             * ====================
             * AP4L Payment Gateway
             * ====================
             */
            add_action('plugins_loaded', array( $this, 'ap4l_new_payment_gateway_file' ));
            add_filter('woocommerce_payment_gateways', array( $this, 'ap4l_new_payment_gateway_class' ));
            add_filter('woocommerce_available_payment_gateways', array( $this, 'custom_available_payment_gateways' ));
            /*
             * ====================
             * AP4L Shipping Gateway
             * ====================
             */
            add_action('woocommerce_shipping_init', array( $this, 'ap4l_new_shipping_gateway_file' ));
            add_filter('woocommerce_shipping_methods', array( $this, 'ap4l_new_shipping_gateway_class' ));
        }
    }
}
