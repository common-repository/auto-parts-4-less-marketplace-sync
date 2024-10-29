<?php
/**
 * The admin-specific functionality of the plugin.
 */
namespace Ap4l;

include_once AP4L_DIR . 'classes/AdminFunctions.php';
include_once AP4L_DIR . 'classes/UserModal.php';

use Ap4l\AdminFunctions;
use Ap4l\UserModal;

if (! class_exists('Admin')) {
    class Admin extends AdminFunctions
    {
        /*
         * ==========================================
         * Initialize the class and set its properties.
         * ==========================================
         */
        public function __construct()
        {
            // NA
        }
        /**
         * ===================================================
         * Add options page's link to plugins page of wordpress
         * ===================================================
         */
        public function ap4l_plugin_action_links($actions)
        {
            array_unshift($actions, '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=ap4l-accounts')) . '">Settings</a>');
            return $actions;
        }
        /*
         * ==========================================
         * Register the stylesheets for the admin area.
         * ==========================================
         */
        public function ap4lEnqueueStyles()
        {
            // custom plugin css
            $page = (!empty($_REQUEST[ 'page' ])) ? sanitize_text_field($_REQUEST[ 'page' ]) : '';

            // if (str_contains($page, AP4L_SLUG)) {
                wp_enqueue_style(AP4L_SLUG . '-select2', AP4L_URL . 'assets/css/select2-4.0.13.min.css', array(), AP4L_VERSION, 'all');
                wp_enqueue_style(AP4L_SLUG . '-admin', AP4L_URL . 'assets/css/admin.css', array(), AP4L_VERSION, 'all');
            // }
        }
        /*
         * ==========================================
         * Register the JavaScript for the admin area.
         * ==========================================
         */
        public function ap4lEnqueueScripts()
        {
            $page = (!empty($_REQUEST[ 'page' ])) ? sanitize_text_field($_REQUEST[ 'page' ]) : '';

            // if (str_contains($page, AP4L_SLUG)) {
                wp_enqueue_script(AP4L_SLUG . '-variables', $this->defineJavascriptVariables(), array( 'jquery' ), AP4L_VERSION, true);
                wp_enqueue_script(AP4L_SLUG . '-select2', AP4L_URL . 'assets/js/select2-4.0.13.min.js', array( 'jquery' ), AP4L_VERSION, true);
                wp_enqueue_script(AP4L_SLUG . '-validate', AP4L_URL . 'assets/js/jquery.validate-1.19.0.min.js', array( 'jquery' ), AP4L_VERSION, true);

                //Chart JS
                wp_enqueue_script(AP4L_SLUG . '-chart', AP4L_URL . 'assets/js/chart.min.js', array( 'jquery' ), time(), false);

                // custom plugin js
                wp_enqueue_script(AP4L_SLUG . '-acc-admin', AP4L_URL . 'assets/js/account-module.js', array( 'jquery' ), time(), true);
                wp_enqueue_script(AP4L_SLUG . '-policy-admin', AP4L_URL . 'assets/js/policy-module.js', array( 'jquery' ), time(), true);
                wp_enqueue_script(AP4L_SLUG . '-listing-admin', AP4L_URL . 'assets/js/listing-module.js', array( 'jquery' ), time(), true);
                wp_enqueue_script(AP4L_SLUG . '-category-admin', AP4L_URL . 'assets/js/category-module.js', array( 'jquery' ), time(), true);
                wp_enqueue_script(AP4L_SLUG . '-general-admin', AP4L_URL . 'assets/js/ap4l-general.js', array( 'jquery' ), time(), true);
            // }
        }
        /*
         * ==========================
         * Define Javascript Variable
         * ==========================
         */
        public function defineJavascriptVariables()
        {
            ?>
            <script type="text/javascript">
                var pluginSlug = '<?php echo esc_html(AP4L_SLUG); ?>';
                var pluginPrefix = '<?php echo esc_html(AP4L_PREFIX); ?>';
                var ajaxUrl = '<?php echo esc_url(admin_url("admin-ajax.php")); ?>';
                var adminUrl = '<?php echo esc_url(admin_url()); ?>';
                var ajaxNonce = '<?php echo esc_html(wp_create_nonce(AP4L_PREFIX . 'nonce')); ?>';
            </script>
            <?php
        }
        /*
         * =========================================
         * Register options page in admin menu area.
         * =========================================
         */
        public function ap4lAddSettingsPage()
        {
            add_menu_page(AP4L_PLUGIN_NAME, AP4L_PLUGIN_NAME, 'manage_options', AP4L_SLUG, array( $this, 'accountsPageHtml' ), 'dashicons-admin-settings', 55);
            add_submenu_page(AP4L_SLUG, 'Accounts', 'Accounts', 'manage_options', AP4L_SLUG . '-accounts', array( $this, 'accountsPageHtml' ));
            remove_submenu_page(AP4L_SLUG, AP4L_SLUG);
            add_submenu_page(AP4L_SLUG, 'Policies', 'Policies', 'manage_options', AP4L_SLUG . '-policies', array( $this, 'PoliciesPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Categories', 'Categories', 'manage_options', AP4L_SLUG . '-categories', array( $this, 'CategoriesPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Listings', 'Listings', 'manage_options', AP4L_SLUG . '-listings', array( $this, 'ListingsPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Listing Logs', 'Listing Logs', 'manage_options', AP4L_SLUG . '-listing-logs', array( $this, 'ListingLogsPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Listings Inner', 'Listings Inner', 'manage_options', AP4L_SLUG . '-listings-inner', array( $this, 'ListingsInnerPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Orders', 'Orders', 'manage_options', AP4L_SLUG . '-orders', array( $this, 'OrdersPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Order Logs', 'Order Logs', 'manage_options', AP4L_SLUG . '-orders-logs', array( $this, 'OrderLogsPageHtml' ));
            add_submenu_page(AP4L_SLUG, 'Log Settings', 'Log Settings', 'manage_options', AP4L_SLUG . '-logs-setting', array( $this, 'LogsSettingPageHtml' ));
        }
        /*
         * ===========================
         * Product Meta Box
         * ==========================
         */
        public function ap4l_product_listing_meta()
        {
            add_meta_box(
                'ap4l-product-listing',
                __('AP4L Product Listing', 'ap4l'),
                array( $this, 'ap4l_product_display_callback' ),
                'product',
                'advanced',
                'high'
            );
        }
        public function ap4l_product_display_callback($post)
        {
            include_once AP4L_DIR . 'views/parts/ap4lProductMetaBox.php';
        }
        public function ap4l_product_save_action($product_id, $post, $update)
        {
            $ap4lProListing = (isset($_REQUEST[ 'ap4lProListing' ])) ? sanitize_text_field($_REQUEST[ 'ap4lProListing' ]) : '';
            $ap4l_shipping_policyid = (isset($_REQUEST[ 'ap4l_shipping_policyid' ])) ? sanitize_text_field($_REQUEST[ 'ap4l_shipping_policyid' ]) : '';

            if (! empty($ap4lProListing)) {
                update_post_meta($product_id, 'ap4l_pro_listing', $ap4lProListing);
            }

            if (! empty($ap4l_shipping_policyid)) {
                update_post_meta($product_id, 'ap4l_shipping_policyid', $ap4l_shipping_policyid);
            }

            $wcMappingAttrs = $this->getWCMappingAttributes();

            if (! empty($wcMappingAttrs)) {
                foreach ($wcMappingAttrs as $map_key => $map_val) {
                    $map_slug = 'ap4l-' . sanitize_title($map_val->attribute_name);

                    if (isset($_REQUEST[ $map_slug ])) {
                        update_post_meta($product_id, $map_slug, sanitize_text_field($_REQUEST[ $map_slug ]));
                    }
                }
            }
        }
        /*
         * ========================
         * Product Filter Functions
         * ========================
         */
        public function ap4l_product_storage_filter($post_type, $which)
        {
            if ($post_type != 'product') {
                return;
            }
            $UserModal   = new UserModal();
            $getListing  = $UserModal->getListing();
            $listing_val = (!empty($_REQUEST['proListingFilter'])) ? sanitize_text_field($_REQUEST['proListingFilter']) : '';

            if (! empty($getListing)) {
                echo wp_kses('<select id="proListingFilter" name="proListingFilter">', AP4L_ALLOWED_HTML);
                echo wp_kses('<option value="all">' . __('Show All Listing', 'ap4l') . ' </option>', AP4L_ALLOWED_HTML);

                foreach ($getListing as $key => $value) {
                    $selected = ($listing_val == $value->id) ? 'selected' : '';
                    echo wp_kses('<option value="' . $value->id . '" ' . $selected . ' >' . $value->listing_name . '</option>', AP4L_ALLOWED_HTML);
                }

                echo wp_kses('</select>', AP4L_ALLOWED_HTML);
            }
        }

        public function ap4l_product_storage_filter_query($query)
        {
            if (! (is_admin() && $query->is_main_query())) {
                return $query;
            }

            if ('product' !== $query->query[ 'post_type' ]) {
                return $query;
            }

            $proListingFilter = (!empty($_REQUEST['proListingFilter'])) ? sanitize_text_field($_REQUEST['proListingFilter']) : '';

            if (!empty($proListingFilter) && $proListingFilter != 'all') {
                $query->query_vars[ 'meta_key' ]     = 'ap4l_pro_listing';
                $query->query_vars[ 'meta_value' ]   = $proListingFilter;
                $query->query_vars[ 'meta_compare' ] = '=';
            }

            return $query;
        }

        /*
         * ===================
         * Bulk Edit Functions
         * ===================
         */
        public function ap4l_product_listing_bulk_edit()
        {
            $UserModal  = new UserModal();
            $getListing = $UserModal->getListing();
            ?>
            <div class="inline-edit-group">
                <label class="alignleft">
                    <span class="title"><?php _e('AP4L Listing', 'woocommerce'); ?></span>
                    <span class="input-text-wrap">
                        <select class="change_listing_bulk change_to" name="ap4lListing">
                            <option value="">— No change —</option>

                            <?php foreach ($getListing as $key => $value) : ?>
                            <option value="<?php echo esc_attr($value->id); ?>"><?php echo esc_html($value->listing_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                </label>
            </div>
            <?php
        }
        public function ap4l_product_listing_bulk_edit_save($product)
        {
            $product_id = method_exists($product, 'get_id') ? $product->get_id() : $product->id;

            $ap4lListing = (!empty($_REQUEST['ap4lListing'])) ? sanitize_text_field($_REQUEST['ap4lListing']) : '';

            if (!empty($ap4lListing)) {
                update_post_meta($product_id, 'ap4l_pro_listing', $ap4lListing);
                update_post_meta($product_id, 'ap4l_pro_listing_date', current_time('Y-m-d H:i:s'));
            }
        }

        /*
         * ===================
         * Quick edit function
         * ===================
         */
        public function ap4l_product_listing_quick_edit()
        {
            global $post;
            $UserModal    = new UserModal();
            $getListing   = $UserModal->getListing();
            $ap4l_listing = get_post_meta($post->ID, 'ap4l_pro_listing', true);
            ?>
            <div class="inline-edit-group">
                <label class="alignleft">
                    <span class="title"><?php _e('AP4L Listing', 'woocommerce'); ?></span>
                    <span class="input-text-wrap">
                        <select class="change_listing_quick change_to" name="ap4lListing">
                            <option value="0">— No change —</option>

                            <?php foreach ($getListing as $key => $value) : ?>
                            <option value="<?php echo esc_attr($value->id); ?>" <?php selected($ap4l_listing, $value->id); ?>><?php echo esc_html($value->listing_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                </label>
            </div>
            <br class="clear">
            <?php
        }

        public function ap4l_product_listing_quick_edit_save($product)
        {
            $product_id = $product->get_id();

            $ap4lListing = (!empty($_REQUEST['ap4lListing'])) ? sanitize_text_field($_REQUEST['ap4lListing']) : '';

            if (!empty($ap4lListing)) {
                update_post_meta($product_id, 'ap4l_pro_listing', $ap4lListing);
                update_post_meta($product_id, 'ap4l_pro_listing_date', current_time('Y-m-d H:i:s'));
            }
        }

        /*
         * ========================
         * Add Column product page
         * ========================
         */
        public function ap4l_products_listing_column($columns)
        {
            $columns[ 'ap4l_listing' ] = 'AP4L Listing';
            return $columns;
        }
        public function ap4l_product_sortable_columns($columns)
        {
            $columns[ 'ap4l_listing' ] = 'ap4l_listing';
            return $columns;
        }
        public function ap4l_products_listing_column_content($column, $product_id)
        {
            if ($column == 'ap4l_listing') {
                $ap4l_listing = get_post_meta($product_id, 'ap4l_pro_listing', true);
                if (! empty($ap4l_listing)) {
                    $UserModal = new UserModal();
                    $listing_info  = $UserModal->getListing($ap4l_listing);
                    echo wp_kses((!empty($listing_info)) ? $listing_info[ 0 ]->listing_name : '-', AP4L_ALLOWED_HTML);
                    echo wp_kses('<div style="display:none"><span id="cf_' . $product_id . '">' . $ap4l_listing . '</span></div>', AP4L_ALLOWED_HTML);
                    wc_enqueue_js("
                    jQuery('#the-list').on('click', '.editinline', function() {
                        var post_id = jQuery(this).closest('tr').attr('id');
                        post_id = post_id.replace('post-', '');
                        var custom_field = jQuery('#cf_' + post_id).text();
                        if(custom_field){
                            jQuery('.change_listing_quick option[value='+custom_field+']').attr('selected','selected');
                        }else{
                            jQuery('.change_listing_quick option').removeAttr('selected');
                        }
                    });
                 ");
                }
            }
        }
        public function ap4l_default_hidden_columns($hidden, $screen)
        {
            if (isset($screen->id) && 'edit-product' === $screen->id) {
                $hidden[] = 'ap4l_listing';
            }
            return $hidden;
        }
        /*
         * ==============================
         * add parameter in wc_get_orders
         * ==============================
         */
        public function ap4l_custom_query_var($query, $query_vars)
        {
            $query[ 'meta_query' ][ 'relation' ] = 'AND';
            if (! empty($query_vars[ 'onlyAP4LOrders' ])) {
                $query[ 'meta_query' ][] = array(
                    'key'     => 'ap4l_order',
                    'value'   => 1,
                    'compare' => '=',
                );
            }
            if (! empty($query_vars[ 'orderStatusFilter' ])) {
                $query[ 'meta_query' ][] = array(
                    'key'     => 'ap4l_order_status',
                    'value'   => $query_vars[ 'orderStatusFilter' ],
                    'compare' => '=',
                );
            }
            return $query;
        }
        /*
         * ======================
         * Add AP4L order status
         * ======================
         */
        public function add_ap4l_order_status_to_order($order)
        {
            $orderId     = $order->get_id();
            $orderStatus = ucwords($order->get_meta('ap4l_order_status'));
            $ap4l_order  = $order->get_meta('ap4l_order');
            if ($ap4l_order) {
                ?>
                <p class="form-field form-field-wide wc-order-status">
                    <label for="order_status"><?php _e('AP4L Order Status:', 'woocommerce'); ?></label>
                    <input type="text" value="<?php echo esc_attr($orderStatus); ?>" readonly=""/>
                </p>
                <?php
            }
        }
        /*
         * ============================
         * Order Meta Box
         * ==========================
         */
        public function ap4l_orders_shipping_meta()
        {
            add_meta_box(
                'ap4l-orders-shipping',
                __('AP4L Order Shipping', 'ap4l'),
                array( $this, 'ap4l_orders_shipping_callback' ),
                'shop_order',
                'side',
                'high'
            );
        }
        public function ap4l_orders_shipping_callback($post)
        {
            include_once AP4L_DIR . 'views/parts/ap4lOrdersMetaBox.php';
        }
        public function ap4l_orders_save_action($product_id, $post, $update)
        {
            $action_req = (!empty($_REQUEST['ap4lProListing'])) ? sanitize_text_field($_REQUEST['ap4lProListing']) : '';

            if (! empty($action_req)) {
                update_post_meta($product_id, 'ap4l_pro_listing', $action_req);
            }

            $wcMappingAttrs = $this->getWCMappingAttributes();

            if (! empty($wcMappingAttrs)) {
                foreach ($wcMappingAttrs as $map_key => $map_val) {
                    $map_slug = 'ap4l-' . sanitize_title($map_val->attribute_name);

                    if (! empty($_REQUEST[ $map_slug ])) {
                        update_post_meta($product_id, $map_slug, sanitize_text_field($_REQUEST[ $map_slug ]));
                    }
                }
            }
        }
        /*
         * ====================================
         * Create Payment & shipping Methods
         * ===================================
         */
        public function ap4l_new_payment_gateway_class($methods)
        {
            $methods[] = 'Ap4lPaymentMethod';
            return $methods;
        }

        function ap4l_new_payment_gateway_file()
        {
            include_once AP4L_DIR . 'classes/Ap4lPaymentMethod.php';
        }

        function ap4l_new_shipping_gateway_class($methods)
        {
            $methods[] = 'Ap4lShippingMethod';
            return $methods;
        }

        function ap4l_new_shipping_gateway_file()
        {
            include_once AP4L_DIR . 'classes/Ap4lShippingMethod.php';
        }

        function custom_available_payment_gateways($available_gateways)
        {
            // Not in backend (admin)
            if (is_admin()) {
                return $available_gateways;
            }
            unset($available_gateways[ 'ap4l_payment' ]);
            return $available_gateways;
        }
    }
}